<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Request\RequestEvent;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class CreateHandleEvent extends RequestEvent
{
    protected $handle;
    
    public function __construct(QueueInterface $queue, RequestInterface $request, ConnectionHandleInterface $handle = null)
    {
        parent::__construct($queue, $request);
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
