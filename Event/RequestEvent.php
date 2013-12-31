<?php
namespace Yjv\HttpQueue\Event;

use Yjv\HttpQueue\Request\RequestInterface;
use Yjv\HttpQueue\Queue\QueueInterface;
use Symfony\Component\EventDispatcher\Event;

class RequestEvent extends Event
{
    protected $queue;
    protected $request;
    
    public function __construct(QueueInterface $queue, RequestInterface $request)
    {
        $this->queue = $queue;
        $this->request = $request;
    }
    
    public function getQueue()
    {
        return $this->queue;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }
}
