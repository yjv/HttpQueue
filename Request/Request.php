<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Connection\PayloadInterface;

use Yjv\HttpQueue\Uri\Uri;

class Request implements RequestInterface
{
    protected $handleOptions = array();
    protected $options = array();
    protected $url;
    protected $method;
    protected $headers;
    protected $body;
    protected $trackProgress = false;
    
    public function __construct($url, $method = RequestInterface::METHOD_GET, $headers = array(), PayloadInterface $body = null)
    {
        $this->setUrl($url);
        $this->setMethod($method);
        $this->setHeaders($headers);
        $this->body = $body;
    }

    public function setHandleOption($name, $value)
    {
        $this->handleOptions[$name] = $value;
        return $this;
    }
    
    public function getHandleOption($name, $default = null)
    {
        if (isset($this->handleOptions[$name]) || array_key_exists($name, $this->handleOptions)) {
            
            return $this->handleOptions[$name];
        }
        
        return $default;
    }
    
    public function getHandleOptions()
    {
        return $this->handleOptions;
    }
    
    public function setUrl($url)
    {
        if (is_string($url)) {
            
            $url = Uri::createFromString($url);
        }
        
        $this->url = $url;
        return $this;
    }
    
    public function getUrl()
    {
        return $this->url;
    }
    
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function setHeaders($headers)
    {
        $this->headers = $headers instanceof RequestHeaderBag ? $headers : new RequestHeaderBag($headers);
        return $this;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function getTrackProgress()
    {
        return $this->trackProgress;
    }
    
    public function setTrackProgress($trackProgress)
    {
        $this->trackProgress = $trackProgress;
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
