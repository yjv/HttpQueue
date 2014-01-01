<?php
namespace Yjv\HttpQueue\Connection;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface MultiHandleInterface
{
    public function addHandle(ConnectionHandleInterface $connection);
    public function removeHandle(ConnectionHandleInterface $connection);
    public function execute();
    public function getHandleResponseContent(ConnectionHandleInterface $connection);
    public function select($timeout = 1.0);
    public function getFinishedHandles();
    public function close();
    public function getResource();
    public function getStillRunningCount();
}
