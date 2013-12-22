<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Request\RequestEvent;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Symfony\Component\EventDispatcher\Event;

class HandleEvent extends RequestEvent
{
    protected $handle;
    protected $args;
    protected $handleEventName;
    
    public function __construct(
        QueueInterface $queue, 
        RequestInterface $request, 
        ConnectionHandleInterface $handle, 
        $handleEventName, 
        $args
    ) {
        parent::__construct($queue, $request);
        $this->handle = $handle;
        $this->handleEventName = $handleEventName;
        $this->args = $args;
    }
    
    public function getHandle()
    {
        return $this->handle;
    }
    
    public function getHandleEventName()
    {
        return $this->handleEventName;
    }
    
    public function getArgs()
    {
        return $this->args;
    }
}
