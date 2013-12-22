<?php
namespace Yjv\HttpQueue\Connection\Payload;

interface SourcePayloadInterface extends PayloadInterface
{
    public function getPayloadData();
    public function getContentType();
    public function getContentLength();
}
