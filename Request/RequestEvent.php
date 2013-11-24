<?php
namespace Yjv\HttpRequest\Request;

use Symfony\Component\EventDispatcher\Event;

class RequestEvent extends Event
{
    protected $queue;
    protected $request;
    protected $response;
    
    public function __construct(RequestInterface $request, QueueInterface $queue, ResponseInterface $response = null)
    {
        $this->request = $request;
        $this->queue = $queue;
        $this->response = $response;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function getQueue()
    {
        return $this->queue;
    }
    
    public function getResponse()
    {
        return $this->response;
    }
}
