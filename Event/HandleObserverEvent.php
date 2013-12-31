<?php
namespace Yjv\HttpQueue\Event;

use Yjv\HttpQueue\Queue\QueueInterface;

use Yjv\HttpQueue\Event\RequestEvent;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class HandleObserverEvent extends HandleEvent
{
    protected $handle;
    protected $args;
    
    public function __construct(
        QueueInterface $queue, 
        RequestInterface $request, 
        ConnectionHandleInterface $handle, 
        array $args
    ) {
        parent::__construct($queue, $request, $handle);
        $this->args = $args;
    }
    
    public function getArgs()
    {
        return $this->args;
    }
    
    public function setArgs(array $args)
    {
        $this->args = $args;
        return $this;
    }
}
