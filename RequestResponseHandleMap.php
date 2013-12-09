<?php
namespace Yjv\HttpQueue;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

class RequestResponseHandleMap
{
    protected $requests;
    protected $responses;
    
    public function __construct()
    {
        $this->requests = new \SplObjectStorage();
        $this->responses = new \SplObjectStorage();
    }
    
    /**
     * 
     * @param CurlHandle $handle curl handle
     * @return \Yjv\HttpQueue\Request\RequestInterface
     */
    public function getRequest(CurlHandleInterface $handle)
    {
        return isset($this->requests[$handle]) ? $this->requests[$handle] : null;
    }
    
    public function setRequest(CurlHandleInterface $handle, RequestInterface $request)
    {
        $this->requests[$handle] = $request;
        return $this;
    }
    
    /**
     * 
     * @param CurlHandle $handle curl handle
     * @return \Yjv\HttpQueue\Response\ResponseInterface
     */
    public function getResponse(CurlHandleInterface $handle)
    {
        return isset($this->responses[$handle]) ? $this->responses[$handle] : null;
    }
    
    /**
     * 
     * @param CurlHandle $handle
     * @param ResponseInterface $response
     * @return \Yjv\HttpQueue\RequestResponseHandleMap
     */
    public function setResponse(CurlHandleInterface $handle, ResponseInterface $response)
    {
        $this->responses[$handle] = $response;
        return $this;
    }
    
    /**
     * 
     * @param CurlHandle $handle
     * @return \Yjv\HttpQueue\RequestResponseHandleMap
     */
    public function clear(CurlHandleInterface $handle = null)
    {
        if ($handle) {
            
            unset($this->requests[$handle], $this->responses[$handle]);
        } else {
            
            $this->requests->removeAll($this->requests);
            $this->responses->removeAll($this->responses);
        }
        
        return $this;
    }
}
