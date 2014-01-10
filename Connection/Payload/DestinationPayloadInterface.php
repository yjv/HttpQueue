<?php
namespace Yjv\HttpQueue\Connection\Payload;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface DestinationPayloadInterface extends PayloadInterface
{
    public function setContentType($contentType);
    public function setContentLength($contentLength);
    public function setSourceHandle(ConnectionHandleInterface $sourceHandle);
}
