<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Symfony\Component\EventDispatcher\Event;

class HandleEvent extends Event
{
    protected $handle;
    protected $args;
    protected $handleEventName;
    
    public function __construct($name, ConnectionHandleInterface $handle, $args)
    {
        $this->handleEventName = $name;
        $this->handle = $handle;
        $this->args = $args;
    }
}
