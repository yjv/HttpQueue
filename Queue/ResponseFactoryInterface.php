<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Transport\HandleInterface;

interface ResponseFactoryInterface
{
    public function registerHandle(HandleInterface $handle, RequestInterface $request);
    public function createResponse(HandleInterface $handle);
}
