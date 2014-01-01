<?php
namespace Yjv\HttpQueue\Connection\Payload;

interface SourcePayloadInterface extends PayloadInterface
{
    public function getContentType();
    public function getContentLength();
}
