<?php
namespace Yjv\HttpQueue\Payload;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Connection\ConnectionInterface;

interface SourcePayloadInterface extends PayloadInterface
{
    public function getPayloadContent();
}
