<?php
namespace Yjv\HttpQueue\Connection;

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
    public function setSourceStream(SourceStreamInterface $sourcePayload);
    public function setDestinationStream(DestinationStreamInterface $destinationPaylod);
    public function setSourcePayload(SourcePayloadInterface $sourcePayload);
    public function setObserver(HandleObserverInterface $observer);
}
