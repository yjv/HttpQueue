<?php
namespace Yjv\HttpQueue\Response;

use Yjv\HttpQueue\Queue\QueueInterface;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Request\RequestEvent;

class HeaderReceivedEvent extends ResponseEvent
{
    protected $header;
    
    public function __construct(QueueInterface $queue, RequestInterface $request, ResponseInterface $response, $header)
    {
        parent::__construct($queue, $request, $response);
        $this->header = $header;
    }
    
    public function getHeader()
    {
        return $this->header;
    }
}
