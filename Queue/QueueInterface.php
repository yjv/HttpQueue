<?php
namespace Yjv\HttpQueue\Queue;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface QueueInterface
{
    public function queue(RequestInterface $request);
    public function unqueue(RequestInterface $request);
    public function send(RequestInterface $request = null);
    public function getCurlMulti();
    public function addEventListener($eventName, $listener, $priority = 0);
    public function addEventSubscriber(EventSubscriberInterface $subscriber);
}
