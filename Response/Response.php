<?php
namespace Yjv\HttpQueue\Response;

use Yjv\HttpQueue\Connection\PayloadInterface;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Response implements ResponseInterface
{
    protected $code;
    protected $headers;
    protected $body;
    
    public function __construct($code, $headers = array(), PayloadInterface $body = null)
    {
        $this->code = $code;
        $this->setHeaders($headers);
        $this->body = $body;
    }
    
    /**
     * @return ResponseHeaderBag
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function setHeaders($headers)
    {
        $this->headers = $headers instanceof ResponseHeaderBag ? $headers : new ResponseHeaderBag($headers);
        return $this;
    }
    
    public function getBody()
    {
        return $this->body;
    }
    
    public function setBody(PayloadInterface $body)
    {
        $this->body = $body;
        return $this;
    }
}
