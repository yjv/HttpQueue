<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface ResponseFactoryInterface
{
    public function registerHandle(ConnectionHandleInterface $handle);
    public function getResponse(ConnectionHandleInterface $handle);
}
