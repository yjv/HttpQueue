<?php
namespace Yjv\HttpQueue\Queue;

interface QueueConfigInterface
{
    public function getEventDispatcher();
    public function getHandleFactory();
    public function getResponseFactory();
    public function getHandleMap();
    public function getMultiHandle();
}
