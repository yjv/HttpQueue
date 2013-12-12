<?php
namespace Yjv\HttpQueue\Response;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Request\RequestEvent;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Queue\QueueInterface;

use Symfony\Component\EventDispatcher\Event;

class ResponseEvent extends RequestEvent
{
    protected $response;
    
    public function __construct(QueueInterface $queue, RequestInterface $request, ResponseInterface $response)
    {
        parent::__construct($queue, $request);
        $this->response = $response;
    }
    
    public function getResponse()
    {
        return $this->response;
    }
}