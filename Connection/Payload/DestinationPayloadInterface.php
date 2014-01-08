<?php
namespace Yjv\HttpQueue\Connection\Payload;

interface DestinationPayloadInterface extends PayloadInterface
{
    public function setContentType($contentType);
    public function setContentLength($contentLength);
}
