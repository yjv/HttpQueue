<?php
namespace Yjv\HttpQueue\Connnection;

use Yjv\HttpQueue\Connection\ConnectionInterface;

interface MultiConnectionInterface
{
    public function addConnection(ConnectionInterface $connection);
    public function removeConnection(ConnectionInterface $connection);
    public function execute(&$stillRunning = 0);
    public function getConnectionResponseContent(ConnectionInterface $connection);
    public function select($selectTimeout = 1.0);
    public function getFinishedConnectionInformation(&$finishedConnectionCount = 0);
    public function close();
    public function getResource();
}
