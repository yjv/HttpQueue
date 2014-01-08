<?php
namespace Yjv\HttpQueue\Connection;

interface HandleObserverInterface
{
    public function notifyHandleEvent($name, ConnectionHandleInterface $handle, array $args);
}
