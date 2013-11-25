<?php
namespace Yjv\HttpQueue\Request;

use Symfony\Component\HttpFoundation\HeaderBag;

class Request implements RequestInterface
{
    protected $curlOptions = array();
    protected $url;
    protected $method;
    protected $headers;
    protected $method;
    
    public function __construct($url, $method = RequestInterface::METHOD_GET, $headers = array(), $body = '')
    {
        $this->setUrl($url);
        $this->setMethod($method);
        $this->setHeaders($headers);
    }
    
    public function getHandle()
    {
        $handle = curl_init();
        
        $curlOptions = $this->curlOptions;
        $curlOptions[CURLOPT_HTTPHEADER] = $this->headers->allPreserveCase();
        $curlOptions[CURLOPT_COOKIE] = $this->headers->getCookies(RequestHeaderBag::COOKIES_STRING);
        
        curl_setopt_array($handle, $curlOptions);
        
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
    
    public function setHeaders($headers)
    {
        $this->headers = $headers instanceof RequestHeaderBag ? $headers : new RequestHeaderBag($headers);
        return $this;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
}
