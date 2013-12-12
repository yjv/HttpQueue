<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Response\HeaderRecievedEvent;

use Yjv\HttpQueue\Response\StatusLineRecievedEvent;

use Yjv\HttpQueue\Response\ResponseEvents;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Yjv\HttpQueue\Response\Response;

use Yjv\HttpQueue\RequestResponseHandleMap;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

class RequestMediator implements RequestMediatorInterface
{
    protected $handleMap;
    protected $dispatcher;
    protected $queue;

    /**
     * Receive a response header from curl
     *
     * @param resource $curl   Curl handle
     * @param string   $header Received header
     *
     * @return int
     */
    public function writeResponseHeader(CurlHandleInterface $handle, $header)
    {
        static $normalize = array("\r", "\n");
        $length = strlen($header);
        $header = str_replace($normalize, '', $header);
        
        if (!$header) {
            
            return;
        }
        
        $request = $this->handleMap->getRequest($handle);
    
        if (strpos($header, 'HTTP/') === 0) {
    
            $startLine = explode(' ', $header, 3);
            $code = $startLine[1];
            $status = isset($startLine[2]) ? $startLine[2] : '';
    
            $response = new Response($code, array());
            $this->handleMap->setResponse($handle, $response);
    
            $this->dispatcher->dispatch(
                ResponseEvents::RECEIVE_STATUS_LINE, 
                new StatusLineRecievedEvent($this->queue, $request, $response, $code, $status)
            );
    
        } elseif ($pos = strpos($header, ':')) {
            $response = $this->handleMap->getResponse($handle);
            $response->getHeaders()->set(
                    trim(substr($header, 0, $pos)),
                    trim(substr($header, $pos + 1)),
                    false
            );
        }
        
        $this->dispatcher->dispatch(ResponseEvents::WRITE_HEADER, new HeaderRecievedEvent($this->queue, $request, $response, $header));
    
        return $length;
    }
    
    /**
     *
     * @see RequestMediatorInterface::progress
     */
    public function progress(CurlHandleInterface $handle, $totalDownloadSize, $amountDownloaded, $totalUploadSize, $amountUploaded)
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
     *
     * @see RequestMediatorInterface::writeResponseBody
     */
    public function writeResponseBody(CurlHandleInterface $handle, $data)
    {
        return strlen($data);
        if ($this->emitIo) {
            $this->request->dispatch('curl.callback.write', array(
                    'request' => $this->request,
                    'write'   => $data
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
     *
     * @see RequestMediatorInterface::readRequestBody
     */
    public function readRequestBody(CurlHandleInterface $handle, $fileDescriptor, $lengthOfDataRead)
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
    
    public function setHandleMap(RequestResponseHandleMap $handleMap)
    {
        $this->handleMap = $handleMap;
        return $this;
    }
    
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }
    
    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
        return $this;
    }
}
