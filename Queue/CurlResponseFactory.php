<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\RequestResponseHandleMap;

use Yjv\HttpQueue\Curl\CurlHandle;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Response\HeaderReceivedEvent;

use Yjv\HttpQueue\Response\StatusLineRecievedEvent;

use Yjv\HttpQueue\Response\HeaderRecievedEvent;

use Yjv\HttpQueue\Response\ResponseEvents;

use Yjv\HttpQueue\Request\RequestEvents;

use Yjv\HttpQueue\Response\Response;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class CurlResponseFactory implements ResponseFactoryInterface
{
    protected $payloadFactory;
    protected $handleMap;
    
    public function __construct(DestinationPayloadFactoryInterface $payloadFactory = null)
    {
        $this->payloadFactory = $payloadFactory ? $payloadFactory : new StreamDestinationPayloadFactory();
        $this->handleMap = new RequestResponseHandleMap();
    }
    
    public function registerHandle(ConnectionHandleInterface $handle, RequestInterface $request)
    {
        $this->handleMap->setRequest($handle, $request);
        $handle->setOption(CURLOPT_HEADERFUNCTION, array($this, 'writeHeader'));
        return $this;
    }
    
    public function createResponse(ConnectionHandleInterface $handle)
    {
        $response = $this->handleMap->getResponse($handle);
        $this->handleMap->clear($handle);
        return $response;
    }
    
    /**
     * Receive a response header from curl
     *
     * @param resource $curl   Curl handle
     * @param string   $header Received header
     *
     * @return int
     */
    public function writeHeader(CurlHandle $handle, $header)
    {
        static $normalize = array("\r", "\n");
        $length = strlen($header);
        $header = str_replace($normalize, '', $header);

        if (!$header) {
            
            return $length;
        }
        
        if (strpos($header, 'HTTP/') === 0) {
    
            $startLine = explode(' ', $header, 3);
            $code = $startLine[1];
            $status = isset($startLine[2]) ? $startLine[2] : '';
    
            $response = new Response($code);
            
            if (!$handle->getOption(CURLOPT_NOBODY, false)) {
            
                $body = $this->payloadFactory->getDestinationPayload(
                    $handle, 
                    $this->handleMap->getRequest($handle), 
                    $response
                );
                $handle->setDestinationStream($body);
                $response->setBody($body);
            }
            
            $this->handleMap->setResponse($handle, $response);
            return $length;
        } 
        
        //if somehow no request is there return 0
        if (!$response = $this->handleMap->getResponse($handle)) {
            
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
    
        return $length;
    }
}
