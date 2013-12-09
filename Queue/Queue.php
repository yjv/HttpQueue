<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

use Yjv\HttpQueue\Curl\CurlMultiInterface;

use Yjv\HttpQueue\Curl\CurlMultiException;

use Yjv\HttpQueue\Curl\CurlMulti;

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
    protected static $multiErrors = array(
        CURLM_BAD_HANDLE      => array('CURLM_BAD_HANDLE', 'The passed-in handle is not a valid CURLM handle.'),
        CURLM_BAD_EASY_HANDLE => array('CURLM_BAD_EASY_HANDLE', "An easy handle was not good/valid. It could mean that it isn't an easy handle at all, or possibly that the handle already is in used by this or another multi handle."),
        CURLM_OUT_OF_MEMORY   => array('CURLM_OUT_OF_MEMORY', 'You are doomed.'),
        CURLM_INTERNAL_ERROR  => array('CURLM_INTERNAL_ERROR', 'This can only be returned if libcurl bugs. Please report it to us!')
    );
    protected $pending;
    protected $sending;
    protected $responses;
    protected $handleMap;
    protected $curlMulti;
    protected $dispatcher;
    protected $sendCalls = 0;
    
    public function __construct(
        CurlMultiInterface $curlMulti = null, 
        EventDispatcherInterface $dispatcher = null
    ) {
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
        $this->handleMap = new RequestResponseHandleMap();
        $this->pending = new \SplObjectStorage();
        $this->sending = new \SplObjectStorage();
        $this->responses = new \SplObjectStorage();
        $this->curlMulti = $curlMulti ?: new CurlMulti();
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
            
            $this->sending->attach($pending);
            $handle = $pending->createHandle()
                ->setOption(CURLOPT_WRITEFUNCTION, array($this, 'writeResponseBody'))
                ->setOption(CURLOPT_HEADERFUNCTION, array($this, 'receiveResponseHeader'))
            ;
            
            $this->handleMap->setRequest($handle, $pending);
            $this->curlMulti->addHandle($handle);
        }
        
        $this->pending->removeAll($this->pending);
        
        $this->executeHandles();   

        $this->sendCalls--;
        
        if (!$this->sendCalls) {
            
            $responses = iterator_to_array($this->responses);
            $this->responses->removeAll($this->responses);
            return $responses;
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
    public function receiveResponseHeader(CurlHandleInterface $handle, $header)
    {
        static $normalize = array("\r", "\n");
        $length = strlen($header);
        $header = str_replace($normalize, '', $header);
    
        if (strpos($header, 'HTTP/') === 0) {
    
            $startLine = explode(' ', $header, 3);
            $code = $startLine[1];
            $status = isset($startLine[2]) ? $startLine[2] : '';
    
            $response = new Response($code, array());
            $this->handleMap->setResponse($handle, $response);
    
//             $this->dispatcher->dispatch(RequestEvents::RECEIVE_STATUS_LINE, array(
//                     'request'       => $this,
//                     'line'          => $header,
//                     'status_code'   => $code,
//                     'reason_phrase' => $status
//             ));
    
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
        return strlen($write);
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
    public function readRequestBody(CurlHandleInterface $handle, $fd, $length)
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
    
    public function getResponse(RequestInterface $request)
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
            while (($mrc = $this->curlMulti->execute($active)) == CurlMultiInterface::STATUS_PERFORMING);
            $this->checkCurlResult($mrc);
            $this->processResults();
            if ($active && $this->curlMulti->select($selectTimeout) === -1) {
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
        if ($code != CurlMultiInterface::STATUS_OK && $code != CurlMultiInterface::STATUS_PERFORMING) {
            
            throw new CurlMultiException($code);
        }
    }
    
   /**
    * Process any received curl multi messages
    */
    protected function processResults()
    {
        while ($done = $this->curlMulti->getFinishedHandleInformation()) {

            $handle = $done->getHandle();
            $this->curlMulti->removeHandle($handle);
            $handle->close();
            $request = $this->handleMap->getRequest($handle);
            $response = $this->handleMap->getResponse($handle);
            
            $event = new RequestEvent($this, $request, $response);
    
            if ($done->getResult() !== CurlMultiInterface::STATUS_OK) {
        
                $this->dispatcher->dispatch(RequestEvents::ERROR, $event);
            }
    
            $this->dispatcher->dispatch(RequestEvents::COMPLETE, $event);
            
            $this->sending->detach($request);
            $this->responses->attach($response);
            $this->handleMap->clear($handle);
        }
    }
}
