<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Curl\CurlMulti;

use Yjv\HttpQueue\RequestResponseHandleMap;

use Symfony\Component\EventDispatcher\EventDispatcher;

class CurlQueueConfig implements QueueConfigInterface
{
    protected $eventDispatcher;
    protected $handleFactory;
    protected $responseFactory;
    protected $handleMap;
    protected $multiHandle;
    
    public function __construct()
    {
        $this->eventDispatcher = new EventDispatcher();
        $this->handleFactory = new CurlHandleFactory();
        $this->responseFactory = new CurlResponseFactory();
        $this->handleMap = new RequestResponseHandleMap();
        $this->multiHandle = new CurlMulti();
    }
    
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }
    
    public function getHandleFactory()
    {
        return $this->handleFactory;
    }
    
    public function getResponseFactory()
    {
        return $this->responseFactory;
    }
    
    public function getHandleMap()
    {
        return $this->handleMap;
    }
    
    public function getMultiHandle()
    {
        return $this->multiHandle;
    }
}
