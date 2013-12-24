<?php
namespace Yjv\HttpQueue\Response;

use Yjv\HttpQueue\Connection\Payload\DestinationPayloadInterface;

class Response implements ResponseInterface
{
    protected $code;
    protected $statusMessage;
    protected $headers;
    protected $body;
    
    public function __construct($code, $headers = array(), DestinationPayloadInterface $body = null)
    {
        $this->code = $code;
        $this->setHeaders($headers);
        $this->body = $body;
    }
    
    public function __toString()
    {
        return $this->headers . "\r\n" . $this->body;
    }
    
    public function getCode()
    {
        return $this->code;
    }
    
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
    
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }
    
    public function setStatusMessage($statusMessage)
    {
        $this->statusMessage = $statusMessage;
        return $this;
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
    
    public function setBody(DestinationPayloadInterface $body)
    {
        $this->body = $body;
        return $this;
    }
}
