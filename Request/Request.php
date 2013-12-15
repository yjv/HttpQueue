<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Url\Url;

class Request implements RequestInterface
{
    protected $handleOptions = array();
    protected $url;
    protected $method;
    protected $headers;
    protected $body;
    protected $trackProgress = false;
    
    public function __construct($url, $method = RequestInterface::METHOD_GET, $headers = array(), $body = '')
    {
        $this->setUrl($url);
        $this->setMethod($method);
        $this->setHeaders($headers);
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
            
            $url = Url::createFromString($url);
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
        return !$this->getHandleOption(CURLOPT_NOPROGRESS, true);
    }
    
    public function setTrackProgress($trackProgress)
    {
        $this->setHandleOption(CURLOPT_NOPROGRESS, !$trackProgress);
        return $this;
    }
}
