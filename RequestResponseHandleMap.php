<?php
namespace Yjv\HttpQueue;

use Yjv\HttpQueue\Request\RequestInterface;

class RequestResponseHandleMap
{
    protected $requests = array();
    protected $responses = array();
    
    /**
     * 
     * @param resource $handle curl handle
     * @return \Yjv\HttpQueue\Request\RequestInterface
     */
    public function getRequest($handle)
    {
        return $this->requests[(int)$handle];
    }
    
    public function setRequest($handle, RequestInterface $request)
    {
        $this->requests[(int)$handle] = $request;
        return $this;
    }
    
    /**
     * 
     * @param resource $handle curl handle
     * @return \Yjv\HttpQueue\Response\ResponseInterface
     */
    public function getResponse($handle)
    {
        return $this->responses[(int)$handle];
    }
    
    public function setResponse($handle, ResponseInterface $response)
    {
        $this->responses[(int)$handle] = $response;
        return $this;
    }
}
