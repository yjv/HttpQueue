<?php
namespace Yjv\HttpQueue\Queue;

interface QueueConfigInterface
{
    /**
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public function getEventDispatcher();
    
    /**
     * @return \Yjv\HttpQueue\Queue\HandleFactoryInterface
     */
    public function getHandleFactory();
    
    /**
     * @return \Yjv\HttpQueue\Queue\ResponseFactoryInterface
     */
    public function getResponseFactory();
    
    /**
     * @return \Yjv\HttpQueue\RequestResponseHandleMap
     */
    public function getHandleMap();
    
    /**
     * @return \Yjv\HttpQueue\Connection\MultiHandleInterface
     */
    public function getMultiHandle();
}
