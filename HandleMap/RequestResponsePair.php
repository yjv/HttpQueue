<?php
namespace Yjv\HttpQueue\HandleMap;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

class RequestResponsePair
{
    protected $request;
    protected $response;
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }
    
    public function getResponse()
    {
        return $this->response;
    }
    
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }
}
