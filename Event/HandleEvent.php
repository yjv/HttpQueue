<?php
namespace Yjv\HttpQueue\Event;

use Yjv\HttpQueue\Queue\QueueInterface;
use Yjv\HttpQueue\Request\RequestInterface;
use Yjv\HttpQueue\Event\RequestEvent;
use Yjv\HttpQueue\Transport\HandleInterface;

class HandleEvent extends RequestEvent
{
    protected $handle;
    
    public function __construct(QueueInterface $queue, RequestInterface $request, HandleInterface $handle = null)
    {
        parent::__construct($queue, $request);
        $this->handle = $handle;
    }
    
    public function getHandle()
    {
        return $this->handle;
    }
    
    public function setHandle(HandleInterface $handle)
    {
        $this->handle = $handle;
        return $this;
    }
}
