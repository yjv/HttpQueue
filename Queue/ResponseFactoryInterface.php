<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Connection\ConnectionInterface;

interface ResponseFactoryInterface
{
    public function createResponse(ConnectionInterface $connection);
}
