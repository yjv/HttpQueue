<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Curl\CurlHandleFactoryInterface;

interface RequestInterface extends CurlHandleFactoryInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_HEAD = 'HEAD';
}
