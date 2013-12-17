<?php
namespace Yjv\HttpQueue\Connection;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface MultiHandleInterface
{
    public function addHandle(ConnectionHandleInterface $connection);
    public function removeHandle(ConnectionHandleInterface $connection);
    public function execute(&$stillRunning = 0);
    public function getHandleResponseContent(ConnectionHandleInterface $connection);
    public function select($selectTimeout = 1.0);
    public function getFinishedHandleInformation(&$finishedConnectionCount = 0);
    public function close();
    public function getResource();
}
