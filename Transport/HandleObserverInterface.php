<?php
namespace Yjv\HttpQueue\Transport;

interface HandleObserverInterface
{
    public function notifyHandleEvent($name, HandleInterface $handle, array $args);
}
