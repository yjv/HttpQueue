<?php
namespace Yjv\HttpQueue\Payload;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

interface SourcePayloadInterface
{
    public function attachDestinationHandle(CurlHandleInterface $handle);
}
