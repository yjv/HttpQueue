<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Response\ResponseInterface;

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
}
