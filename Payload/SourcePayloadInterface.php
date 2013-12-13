<?php
namespace Yjv\HttpQueue\Payload;

use Yjv\HttpQueue\Connection\ConnectionInterface;

interface SourcePayloadInterface
{
    public function attachDestinationConnection(ConnectionInterface $connection);
}
