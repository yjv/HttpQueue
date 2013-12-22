<?php
namespace Yjv\HttpQueue\Connection\Payload;

interface PayloadInterface
{
    public function setHandle(ConnectionHandleInterface $handle);
    public function __toString();
}
