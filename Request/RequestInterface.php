<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Queue\RequestMediatorInterface;

use Yjv\HttpQueue\Curl\CurlHandleFactoryInterface;

interface RequestInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_HEAD = 'HEAD';
    
    public function getHandleOptions();
    public function getMethod();
    
    /**
     * @return \Yjv\HttpQueue\Request\RequestHeaderBag
     */
    public function getHeaders();
    public function getUrl();
    public function getBody();
}
