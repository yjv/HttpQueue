<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Response\HeaderReceivedEvent;

use Yjv\HttpQueue\Response\StatusLineRecievedEvent;

use Yjv\HttpQueue\Response\HeaderRecievedEvent;

use Yjv\HttpQueue\Response\ResponseEvents;

use Yjv\HttpQueue\Request\RequestEvents;

use Yjv\HttpQueue\Response\Response;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class CurlResponseFactory implements ResponseBuilderInterface
{
    protected $queue;
    protected $handles;
    
    public function __construct(DestinationPayloadFactoryInterface $payloadFactory = null)
    {
        $this->payloadFactory = $payloadFactory ? $payloadFactory : new StreamDestinationPayloadFactory();
    }
    
    public function setQueue(QueueInterface $queue)
    {
        $this->queue = $queue;
        return $this;
    }
    
    public function registerHandle(ConnectionHandleInterface $handle, RequestInterface $request)
    {
        $handle->setOption(CURLOPT_HEADERFUNCTION, array($this, 'writeHeader'));
        return $this;
    }
    
    public function getResponse(ConnectionHandleInterface $handle)
    {
        return $this->queue->getConfig()->getHandleMap()->getResponse($handle);
    }
    
    /**
     * Receive a response header from curl
     *
     * @param resource $curl   Curl handle
     * @param string   $header Received header
     *
     * @return int
     */
    public function writeHeader(CurlHandleInterface $handle, $header)
    {
        static $normalize = array("\r", "\n");
        $length = strlen($header);
        $header = str_replace($normalize, '', $header);
        
        if (strpos($header, 'HTTP/') === 0) {
    
            $startLine = explode(' ', $header, 3);
            $code = $startLine[1];
            $status = isset($startLine[2]) ? $startLine[2] : '';
    
            $response = new Response($code, array());
            $this->queue->getConfig()->getHandleMap()->setResponse($handle, $response);
            $this->queue->getConfig()->getEventDispatcher()->dispatch(
                ResponseEvents::RECEIVE_STATUS_LINE, 
                new StatusLineRecievedEvent(
                    $this->queue, 
                    $this->queue->getConfig()->getHandleMap()->getRequest($handle), 
                    $response, 
                    $code, 
                    $status
                )
            );
            
            return $length;
        } 
        
        //if somehow no request is there return 0
        if (!$response = $this->queue->getConfig()->getHandleMap()->getResponse($handle)) {
            
            return 0;
        }
        
        if (!$pos = strpos($header, ':')) {
            
            return 0;
        }
        
        $response->getHeaders()->set(
                trim(substr($header, 0, $pos)),
                trim(substr($header, $pos + 1)),
                false
        );
        
        $this->queue->getConfig()->getEventDispatcher()->dispatch(
            ResponseEvents::HEADER_RECEIVED,
            new HeaderReceivedEvent(
                $this->queue, 
                $this->queue->getConfig()->getHandleMap()->getRequest($handle), 
                $response, 
                $header
            )
        );
    
        return $length;
    }
}
