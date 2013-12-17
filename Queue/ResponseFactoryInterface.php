<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface ResponseFactoryInterface
{
    public function registerHandle(ConnectionHandleInterface $handle, RequestInterface $request);
    public function createResponse(ConnectionHandleInterface $handle);
}
