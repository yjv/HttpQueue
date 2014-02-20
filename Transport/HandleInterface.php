<?php
namespace Yjv\HttpQueue\Transport;

use Yjv\HttpQueue\Transport\Payload\PayloadDestinationInterface;

use Yjv\HttpQueue\Transport\Payload\PayloadSourceInterface;

interface HandleInterface
{
    public function getResource();
    public function setOptions(array $options);
    public function setOption($name, $value);
    public function getOptions();
    public function getOption($name, $default = null);
    public function execute();
    public function getLastTransferInfo($option = null);
    public function close();
    public function setStreamSource(StreamSourceInterface $sourceStream);
    public function setStreamDestination(StreamDestinationInterface $destinationStream);
    public function setObserver(HandleObserverInterface $observer);
}
