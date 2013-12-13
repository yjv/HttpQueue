<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Request\RequestInterface;

interface ConnectionFactoryInterface
{
    public function createConnection(RequestInterface $request);
}
