<?php
namespace Yjv\HttpQueue;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

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
     * @param ConnectionInterface $handle
     * @return \Yjv\HttpQueue\Request\RequestInterface
     */
    public function getRequest(ConnectionHandleInterface $handle)
    {
        return isset($this->requests[$handle]) ? $this->requests[$handle] : null;
    }
    
    /**
     * 
     * @return array
     */
    public function getRequests()
    {
        $requests = array();
        
        foreach ($this->requests as $handle) {
            
            $requests[] = $this->requests[$handle];
        }
        
        return $requests;
    }
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @param RequestInterface $request
     * @return \Yjv\HttpQueue\RequestResponseHandleMap
     */
    public function setRequest(ConnectionHandleInterface $handle, RequestInterface $request)
    {
        $this->requests[$handle] = $request;
        return $this;
    }
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @return \Yjv\HttpQueue\Response\ResponseInterface
     */
    public function getResponse(ConnectionHandleInterface $handle)
    {
        return isset($this->responses[$handle]) ? $this->responses[$handle] : null;
    }

    /**
     * 
     * @return array
     */
    public function getResponses()
    {
        $responses = array();
    
        foreach ($this->responses as $handle) {
    
            $responses[] = $this->responses[$handle];
        }
    
        return $responses;
    }    
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @param ResponseInterface $response
     * @return \Yjv\HttpQueue\RequestResponseHandleMap
     */
    public function setResponse(ConnectionHandleInterface $handle, ResponseInterface $response)
    {
        $this->responses[$handle] = $response;
        return $this;
    }
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @return \Yjv\HttpQueue\RequestResponseHandleMap
     */
    public function clear(ConnectionHandleInterface $handle = null)
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
