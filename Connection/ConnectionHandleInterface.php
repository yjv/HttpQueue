<?php
namespace Yjv\HttpQueue\Connection;

use Yjv\HttpQueue\Connection\Payload\DestinationPayloadInterface;

use Yjv\HttpQueue\Connection\Payload\SourcePayloadInterface;

interface ConnectionHandleInterface
{
    public function getResource();
    public function setOptions(array $options);
    public function setOption($name, $value);
    public function getOptions();
    public function getOption($name, $default = null);
    public function execute();
    public function getLastTransferInfo($option = null);
    public function close();
    public function setSourcePayload(SourcePayloadInterface $sourcePayload);
    public function setDestinationPayload(DestinationPayloadInterface $destinationPayload);
    public function setObserver(HandleObserverInterface $observer);
}
