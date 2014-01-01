<?php
namespace Yjv\HttpQueue\Event;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Event\RequestEvent;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Queue\QueueInterface;

use Symfony\Component\EventDispatcher\Event;

class ResponseEvent extends RequestEvent
{
    protected $response;
    
    public function __construct(QueueInterface $queue, RequestInterface $request, ResponseInterface $response = null)
    {
        parent::__construct($queue, $request);
        $this->response = $response;
    }
    
    public function getResponse()
    {
        return $this->response;
    }
    
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }
}
