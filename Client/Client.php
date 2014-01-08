<?php
namespace Yjv\HttpQueue\Client;

use Yjv\HttpQueue\Queue\QueueInterface;

class Client implements ClientInterface
{
    protected $queue;
    protected $requestFactory;
    protected $responseProcessor;
    
    public function __construct(
        QueueInterface $queue, 
        RequestFactoryInterface $requestFactory, 
        ResponseProcessorInterface $responseProcessor
    ) {
        $this->queue = $queue;
        $this->requestFactory = $requestFactory;
        $this->responseProcessor = $responseProcessor;
    }
    
    public function getQueue()
    {
        return $this->queue;
    }
}
