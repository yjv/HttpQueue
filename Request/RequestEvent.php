<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Queue\QueueInterface;

use Symfony\Component\EventDispatcher\Event;

class RequestEvent extends Event
{
    protected $queue;
    protected $request;
    protected $response;
    
    public function __construct(QueueInterface $queue, RequestInterface $request, ResponseInterface $response = null)
    {
        $this->queue = $queue;
        $this->request = $request;
        $this->response = $response;
    }
    
    public function getQueue()
    {
        return $this->queue;
    }
    
    public function getRequest()
    {
        return $this->request;
    }
    
    public function getResponse()
    {
        return $this->response;
    }
}
