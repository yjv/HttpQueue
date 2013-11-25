<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Response\Response;

use Yjv\HttpQueue\RequestResponseHandleMap;

use Yjv\HttpQueue\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Request\RequestEvent;

use Yjv\HttpQueue\Request\RequestEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Queue implements QueueInterface
{
    protected $pending;
    protected $sending;
    protected $finished;
    protected $responses;
    protected $handleMap;
    protected $curlMulti;
    protected $dispatcher;
    protected $sendCalls = 0;
    
    public function __construct(EventDispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
        $this->handleMap = new RequestResponseHandleMap();
        $this->pending = new \SplObjectStorage();
        $this->sending = new \SplObjectStorage();
        $this->finished = new \SplObjectStorage();
        $this->responses = new \SplObjectStorage();
        $this->curlMulti = curl_multi_init();
    }
    
    public function __destruct()
    {
        curl_multi_close($this->curlMulti);
    }
    
    public function queue(RequestInterface $request)
    {
        $this->pending->attach($request);
        return $this;
    }
    
    public function unqueue(RequestInterface $request)
    {
        $this->pending->detach($request);
        return $this;
    }
    
    public function send(RequestInterface $request = null)
    {
        $this->sendCalls++;
        
        if ($request) {
            
            $this->queue($request);
        }
        
        foreach ($this->pending as $pending) {
            
            $this->pending->detach($pending);
            $this->sending->attach($pending);
            $handle = $pending->getHandle();
            $this->handleMap->setRequest($handle, $pending);
            curl_multi_add_handle($this->curlMulti, $handle);
        }
        
        $this->executeHandles();   

        $this->sendCalls--;
        
        if (!$this->sendCalls) {
            
            return $this->finished;
        }
    }
    
    public function getCurlMulti()
    {
        return $this->curlMulti;
    }
    
    public function addEventListener($eventName, $listener, $priority = 0)
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
        return $this;
    }
    
    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->addSubscriber($subscriber);
        return $this;
    }
    
    /**
     * Receive a response header from curl
     *
     * @param resource $curl   Curl handle
     * @param string   $header Received header
     *
     * @return int
     */
    public function receiveResponseHeader($handle, $header)
    {
        static $normalize = array("\r", "\n");
        $length = strlen($header);
        $header = str_replace($normalize, '', $header);
    
        if (strpos($header, 'HTTP/') === 0) {
    
            $startLine = explode(' ', $header, 3);
            $code = $startLine[1];
            $status = isset($startLine[2]) ? $startLine[2] : '';
    
            // Only download the body of the response to the specified response
            // body when a successful response is received.
            if ($code >= 200 && $code < 300) {
                $body = $this->request->getResponseBody();
            } else {
                $body = EntityBody::factory();
            }
    
            $response = new Response($code, array(), $body);
            $this->handleMap->setResponse($handle, $response);
            $response->setStatus($code, $status);
            $this->request->startResponse($response);
    
            $this->dispatcher->dispatch(RequestEvents::RECEIVE_STATUS_LINE, array(
                    'request'       => $this,
                    'line'          => $header,
                    'status_code'   => $code,
                    'reason_phrase' => $status
            ));
    
        } elseif ($pos = strpos($header, ':')) {
            $response = $this->handleMap->getResponse($handle);
            $response->getHeaders()->set(
                    trim(substr($header, 0, $pos)),
                    trim(substr($header, $pos + 1)),
                    false
            );
        }
    
        return $length;
    }
    
    /**
     * Received a progress notification
     *
     * @param int        $downloadSize Total download size
     * @param int        $downloaded   Amount of bytes downloaded
     * @param int        $uploadSize   Total upload size
     * @param int        $uploaded     Amount of bytes uploaded
     * @param resource   $handle       CurlHandle object
     */
    public function progress($downloadSize, $downloaded, $uploadSize, $uploaded, $handle = null)
    {
        $this->request->dispatch('curl.callback.progress', array(
                'request'       => $this->request,
                'handle'        => $handle,
                'download_size' => $downloadSize,
                'downloaded'    => $downloaded,
                'upload_size'   => $uploadSize,
                'uploaded'      => $uploaded
        ));
    }
    
    /**
     * Write data to the response body of a request
     *
     * @param resource $curl  Curl handle
     * @param string   $write Data that was received
     *
     * @return int
     */
    public function writeResponseBody($curl, $write)
    {
        if ($this->emitIo) {
            $this->request->dispatch('curl.callback.write', array(
                    'request' => $this->request,
                    'write'   => $write
            ));
        }
    
        if ($response = $this->request->getResponse()) {
            return $response->getBody()->write($write);
        } else {
            // Unexpected data received before response headers - abort transfer
            return 0;
        }
    }
    
    /**
     * Read data from the request body and send it to curl
     *
     * @param resource $ch     Curl handle
     * @param resource $fd     File descriptor
     * @param int      $length Amount of data to read
     *
     * @return string
     */
    public function readRequestBody($ch, $fd, $length)
    {
        if (!($body = $this->request->getBody())) {
            return '';
        }
    
        $read = (string) $body->read($length);
        if ($this->emitIo) {
            $this->request->dispatch('curl.callback.read', array('request' => $this->request, 'read' => $read));
        }
    
        return $read;
    }
    
    public function getResposnes(RequestInterface $request)
    {
        if (!isset($this->responses[$request])) {
            
            return array();
        }
        
        return $this->responses[$request];
    }

    /**
     * Execute and select curl handles
     */
    protected function executeHandles()
    {
        // The first curl_multi_select often times out no matter what, but is usually required for fast transfers
        $selectTimeout = 0.001;
        $active = false;
        do {
            while (($mrc = curl_multi_exec($this->curlMulti, $active)) == CURLM_CALL_MULTI_PERFORM);
            $this->checkCurlResult($mrc);
            $this->processResults();
            if ($active && curl_multi_select($this->curlMulti, $selectTimeout) === -1) {
                // Perform a usleep if a select returns -1: https://bugs.php.net/bug.php?id=61141
                usleep(150);
            }
            $selectTimeout = 1;
        } while ($active);
    }
    
    /**
     * Throw an exception for a cURL multi response if needed
     *
     * @param int $code Curl response code
     * @throws CurlException
     */
    protected function checkCurlResult($code)
    {
        $multiErrors = CurlMultiInterface::MULTI_ERRORS;
    
        if ($code != CurlMultiInterface::CURL_STATUS_OK && $code != CURLM_CALL_MULTI_PERFORM) {
            throw new CurlException(isset($multiErrors[$code])
                    ? "cURL error: {$code} ({$multiErrors[$code][0]}): cURL message: {$multiErrors[$code][1]}"
                    : 'Unexpected cURL error: ' . $code
            );
        }
    }
    
   /**
    * Process any received curl multi messages
    */
    protected function processResults()
    {
        while ($done = curl_multi_info_read($this->curlMulti)) {
            $handle = $done['handle'];
            $result = $info['result'];
    
            curl_multi_remove_handle($this->curlMulti, $handle);
    
            $event = new RequestEvent($this, $this->handleRequestHash[(int)$handle]);
    
            if ($result !== CurlMultiInterface::CURL_STATUS_OK) {
        
                $this->dispatcher->dispatch(RequestEvents::ERROR, $event);
            }
    
            $this->dispatcher->dispatch(RequestEvents::COMPLETE, $event);
            
        }
    }
    
    protected function addResponse(RequestInterface $request, ResponseInterface $response)
    {
        if (!is_array($this->responses[$request])) {
            
            $this->responses[$request] = array();
        }
        
        $this->resposnes[$request][] = $response;
    }
}
