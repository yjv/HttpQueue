<?php
namespace Yjv\HttpQueue\Event;

use Yjv\HttpQueue\Queue\QueueInterface;
use Yjv\HttpQueue\Request\RequestInterface;
use Yjv\HttpQueue\Event\RequestEvent;
use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class HandleEvent extends RequestEvent
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
