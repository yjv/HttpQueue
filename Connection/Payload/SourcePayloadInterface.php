<?php
namespace Yjv\HttpQueue\Connection\Payload;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface SourcePayloadInterface extends PayloadInterface
{
    public function getContentType();
    public function getContentLength();
    public function setDestinationHandle(ConnectionHandleInterface $destinationHandle);
}
