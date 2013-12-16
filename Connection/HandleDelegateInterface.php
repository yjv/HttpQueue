<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface HandleDelegateInterface
{
    public function handleEvent($name, ConnectionHandleInterface $handle, array $args);
}
