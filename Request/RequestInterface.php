<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Queue\RequestMediatorInterface;

use Yjv\HttpQueue\Curl\CurlHandleFactoryInterface;

interface RequestInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_HEAD = 'HEAD';
    
    public function getCurlOptions();
    public function getMethod();
    public function getHeaders();
}
