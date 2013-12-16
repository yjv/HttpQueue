<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface ResponseBuilderInterface
{
    public function registerHandle(ConnectionHandleInterface $handle, RequestInterface $request);
    public function getResponse(ConnectionHandleInterface $handle);
    public function setQueue(QueueInterface $queue);
}
