<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Queue\RequestMediatorInterface;

use Yjv\HttpQueue\Curl\CurlHandleFactoryInterface;

interface RequestInterface extends CurlHandleFactoryInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_HEAD = 'HEAD';
    
    public function setRequestMediator(RequestMediatorInterface $requestMediator);
}
