<?php
namespace Yjv\HttpQueue\Payload;

use Yjv\HttpQueue\Connection\ConnectionInterface;

interface DestinationPayloadInterface
{
    public function attachSourceConnection(ConnectionInterface $connection);
}
