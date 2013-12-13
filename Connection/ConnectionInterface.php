<?php
namespace Yjv\HttpQueue\Connection;

interface ConnectionInterface
{
    public function getResource();
    public function setOptions(array $options);
    public function setOption($name, $value);
    public function getOptions();
    public function execute();
    public function getLastTransferInfo($option = null);
    public function close();
}
