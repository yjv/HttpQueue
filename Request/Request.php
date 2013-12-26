<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Connection\Payload\PayloadInterface;

use Yjv\HttpQueue\Uri\Factory as UriFactory;

class Request implements RequestInterface
{
    protected $handleOptions = array();
    protected $options = array();
    protected $url;
    protected $method;
    protected $headers;
    protected $body;
    
    public function __construct($url, $method = RequestInterface::METHOD_GET, $headers = array())
    {
        $this->setUrl($url);
        $this->setMethod($method);
        $this->setHeaders($headers);
    }
    
    public function __toString()
    {
        return $this->headers . "\r\n" . $this->body;
    }
    
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }
    
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }
    
    public function getOptions()
    {
        return $this->options;
    }

    public function setHandleOption($name, $value)
    {
        $this->handleOptions[$name] = $value;
        return $this;
    }
    
    public function getHandleOption($name, $default = null)
    {
        return isset($this->handleOptions[$name]) ? $this->handleOptions[$name] : $default;
    }
    
    public function getHandleOptions()
    {
        return $this->handleOptions;
    }
    
    public function setUrl($url)
    {
        if (is_string($url)) {
            
            $url = UriFactory::createUriFromString($url);
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
