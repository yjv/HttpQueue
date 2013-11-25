<?php
namespace Yjv\HttpQueue\Response;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Response implements ResponseInterface
{
    protected $code;
    protected $headers;
    protected $body;
    
    public function __construct($code, $headers = array(), $body = '')
    {
        $this->code = $code;
        $this->setHeaders($headers);
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
}
