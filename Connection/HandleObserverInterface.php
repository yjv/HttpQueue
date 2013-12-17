<?php
namespace Yjv\HttpQueue\Connection;

interface HandleObserverInterface
{
    public function handleEvent($name, ConnectionHandleInterface $handle, array $args);
}
