<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Queue\RequestMediatorInterface;

use Yjv\HttpQueue\Curl\CurlHandleFactoryInterface;

interface RequestInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_HEAD = 'HEAD';
    
    /**
     * @return string
     */
    public function getMethod();
    
    /**
     * @return \Yjv\HttpQueue\Request\RequestHeaderBag
     */
    public function getHeaders();
    
    /**
     * @return \Yjv\HttpQueue\Uri\Uri
     */
    public function getUrl();
    
    /**
     * @return \Yjv\HttpQueue\Connection\Payload\SourcePayloadInterface
     */
    public function getBody();
    
    /**
     * @return array
     */
    public function getOptions(); 
    
    /**
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getOption($name, $default = null);
    
    /**
     * @return array
     */
    public function getHandleOptions();
    
    /**
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getHandleOption($name, $default = null);
}
