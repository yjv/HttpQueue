<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Request\RequestInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface QueueInterface
{
    public function queue(RequestInterface $request);
    public function unqueue(RequestInterface $request);
    public function send();
    public function getConfig();
    public function addEventListener($eventName, $listener, $priority = 0);
    public function addEventSubscriber(EventSubscriberInterface $subscriber);
}
