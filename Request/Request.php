<?php
namespace Yjv\HttpRequest\Request;

class Request implements RequestInterface
{
    protected $curlOptions = array();
    protected $url;
    protected $method;
    
    public function __construct($url, $method = RequestInterface::METHOD_GET)
    {
        $this->setUrl($url);
        $this->setMethod($method);
    }
    
    public function getHandle()
    {
        $handle = curl_init();
        
        foreach ($this->curlOptions as $name => $value) {
            
            curl_setopt($handle, $name, $value);
        }
        
        return $handle;
    }
    
    public function setCurlOption($name, $value)
    {
        $this->curlOptions[$name] = $value;
        return $this;
    }
    
    public function setUrl($url)
    {
        $this->url = $url;
        $this->setCurlOption(CURLOPT_URL, $url);
        return $this;
    }
    
    public function getUrl()
    {
        return $this->url;
    }
    
    public function setMethod($method)
    {
        $this->method = $method;
        
        if ($method == RequestInterface::METHOD_GET) {
            
            return $this->setCurlOption(CURLOPT_HTTPGET, true);
        }
        
        return $this->setCurlOption(CURLOPT_CUSTOMREQUEST, $method);
    }
    
    public function getMethod()
    {
        return $this->method;
    }
}
