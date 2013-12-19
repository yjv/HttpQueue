<?php
namespace Yjv\HttpQueue\Connection;

interface PayloadInterface
{
    public function setHandle(ConnectionHandleInterface $handle);
    public function getContentType();
    public function __toString();
}
