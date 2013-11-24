<?php
namespace Yjv\HttpRequest\Curl;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface CurlMultiInterface
{
    public function addHandle($handle);
    
    public function removeHandle($handle);
    
    public function execute($handle = null);
    
    public function getMultiHandle();
    
    public function addEventListener($eventName, $listener, $priority = 0);
    
    public function addEventSubscriber(EventSubscriberInterface $subscriber);
}
