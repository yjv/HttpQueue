<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Queue\RequestMediatorInterface;

use Yjv\HttpQueue\Url\Url;

use Yjv\HttpQueue\Curl\CurlHandle;

use Symfony\Component\HttpFoundation\HeaderBag;

class Request implements RequestInterface
{
    protected $curlOptions;
    protected $url;
    protected $method;
    protected $headers;
    protected $body;
    protected $requestMediator;
    
    public function __construct($url, $method = RequestInterface::METHOD_GET, $headers = array(), $body = '')
    {
        $this->curlOptions = array(
                CURLOPT_CONNECTTIMEOUT => 150,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_HEADER         => false,
                // Verifies the authenticity of the peer's certificate
                CURLOPT_SSL_VERIFYPEER => 1,
                // Certificate must indicate that the server is the server to which you meant to connect
                CURLOPT_SSL_VERIFYHOST => 2
        );
        $this->setUrl($url);
        $this->setMethod($method);
        $this->setHeaders($headers);
    }

    public function setCurlOption($name, $value)
    {
        $this->curlOptions[$name] = $value;
        return $this;
    }
    
    public function getCurlOption($name, $default = null)
    {
        if (isset($this->curlOptions[$name]) || array_key_exists($name, $this->curlOptions)) {
            
            return $this->curlOptions[$name];
        }
        
        return $default;
    }
    
    public function setUrl($url)
    {
        if (is_string($url)) {
            
            $url = Url::createFromString($url);
        }
        
        $this->url = $url;
        $this->setCurlOption(CURLOPT_URL, (string)$url);
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
    
    public function setRequestMediator(RequestMediatorInterface $requestMediator)
    {
        $this->requestMediator = $requestMediator;
        return $this;
    }
    
    public function getTrackProgress()
    {
        return !$this->getCurlOption(CURLOPT_NOPROGRESS, true);
    }
    
    public function setTrackProgress($trackProgress)
    {
        $this->setCurlOption(CURLOPT_NOPROGRESS, !$trackProgress);
        return $this;
    }
}
