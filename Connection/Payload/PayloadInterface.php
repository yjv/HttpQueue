<?php
namespace Yjv\HttpQueue\Connection\Payload;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface PayloadInterface
{
    public function setHandle(ConnectionHandleInterface $handle);
    public function __toString();
}
