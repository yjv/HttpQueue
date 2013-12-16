<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Request\RequestInterface;

interface HandleFactoryInterface
{
    public function createHandle(RequestInterface $request);
    public function setQueue(QueueInterface $queue);
}
