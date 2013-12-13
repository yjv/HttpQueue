<?php
namespace Yjv\HttpQueue\Payload;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

interface DestinationPayloadInterface
{
    public function attachSourceHandle(CurlHandleInterface $handle);
}
