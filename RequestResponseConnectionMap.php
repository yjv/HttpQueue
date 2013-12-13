<?php
namespace Yjv\HttpQueue;

use Yjv\HttpQueue\Connection\ConnectionInterface;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

class RequestResponseConnectionMap
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
    public function getRequest(ConnectionInterface $connection)
    {
        return isset($this->requests[$connection]) ? $this->requests[$connection] : null;
    }
    
    /**
     * 
     * @return array
     */
    public function getRequests()
    {
        $requests = array();
        
        foreach ($this->requests as $connection) {
            
            $requests[] = $this->requests[$connection];
        }
        
        return $requests;
    }
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @param RequestInterface $request
     * @return \Yjv\HttpQueue\RequestResponseHandleMap
     */
    public function setRequest(ConnectionInterface $connection, RequestInterface $request)
    {
        $this->requests[$connection] = $request;
        return $this;
    }
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @return \Yjv\HttpQueue\Response\ResponseInterface
     */
    public function getResponse(ConnectionInterface $connection)
    {
        return isset($this->responses[$connection]) ? $this->responses[$connection] : null;
    }

    /**
     * 
     * @return array
     */
    public function getResponses()
    {
        $responses = array();
    
        foreach ($this->responses as $connection) {
    
            $requests[] = $this->responses[$connection];
        }
    
        return $responses;
    }    
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @param ResponseInterface $response
     * @return \Yjv\HttpQueue\RequestResponseHandleMap
     */
    public function setResponse(ConnectionInterface $connection, ResponseInterface $response)
    {
        $this->responses[$connection] = $response;
        return $this;
    }
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @return \Yjv\HttpQueue\RequestResponseHandleMap
     */
    public function clear(ConnectionInterface $connection = null)
    {
        if ($connection) {
            
            unset($this->requests[$connection], $this->responses[$connection]);
        } else {
            
            $this->requests->removeAll($this->requests);
            $this->responses->removeAll($this->responses);
        }
        
        return $this;
    }
}
