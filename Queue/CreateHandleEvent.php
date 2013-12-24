<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Queue\QueueInterface;

class CreateHandleEvent extends RequestEvent
{
    protected $handle;
    
    public function __construct(QueueInterface $queue, RequestInterface $request, ConnectionHandleInterface $handle = null)
    {
        $this->handle = $handle;
    }
    
    public function getHandle()
    {
        return $this->handle;
    }
    
    public function setHandle(ConnectionHandleInterface $handle)
    {
        $this->handle = $handle;
        return $this;
    }
}
