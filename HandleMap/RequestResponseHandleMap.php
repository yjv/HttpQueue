<?php
namespace Yjv\HttpQueue\HandleMap;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

class RequestResponseHandleMap
{
    protected $pairs;
    
    public function __construct()
    {
        $this->pairs = new \SplObjectStorage();
    }
    
    /**
     * 
     * @param ConnectionInterface $handle
     * @return \Yjv\HttpQueue\Request\RequestInterface
     */
    public function getRequest(ConnectionHandleInterface $handle)
    {
        return isset($this->pairs[$handle]) ? $this->pairs[$handle]->getRequest() : null;
    }
    
    /**
     * 
     * @return array
     */
    public function getRequests()
    {
        $requests = array();
        
        foreach ($this->pairs as $handle) {
            
            if ($this->pairs[$handle]->getRequest()) {

                $requests[] = $this->pairs[$handle]->getRequest();
            }
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
        $this->initializePair($handle);
        $this->pairs[$handle]->setRequest($request);
        return $this;
    }
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @return \Yjv\HttpQueue\Response\ResponseInterface
     */
    public function getResponse(ConnectionHandleInterface $handle)
    {
        return isset($this->pairs[$handle]) ? $this->pairs[$handle]->getResponse() : null;
    }

    /**
     * 
     * @return array
     */
    public function getResponses()
    {
        $responses = array();
    
        foreach ($this->pairs as $handle) {
    
            $responses[] = $this->pairs[$handle]->getResponse();
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
        $this->initializePair($handle);
        $this->pairs[$handle]->setResponse($response);
        return $this;
    }
    
    public function getHandles($requestOrResponse = null)
    {
        if (!$requestOrResponse) {
            
            return iterator_to_array($this->pairs);
        }
        
        if (!$requestOrResponse instanceof RequestInterface && !$requestOrResponse instanceof ResponseInterface) {
            
            throw new InvalidArgumentException('$requestOrResponse must be an instance of RequestInterface or ResponseInterface');
        }
        
        $handles = array();
        
        foreach ($this->pairs as $handle) {
            
            if (
                $this->pairs[$handle]->getRequest() === $requestOrResponse 
                || $this->pairs[$handle]->getResponse() === $requestOrResponse
            ) {
                $handles[] = $handle;
            }
        }
        
        return $handles;
    }
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @return \Yjv\HttpQueue\RequestResponseHandleMap
     */
    public function clear(ConnectionHandleInterface $handle = null)
    {
        if ($handle) {
            
            unset($this->pairs[$handle]);
        } else {
            
            $this->pairs->removeAll($this->pairs);
        }
        
        return $this;
    }
    
    protected function initializePair(ConnectionHandleInterface $handle)
    {
        if (!isset($this->pairs[$handle])) {
            
            $this->pairs[$handle] = new RequestResponsePair();
        }
    }
}
