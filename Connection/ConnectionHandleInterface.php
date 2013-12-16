<?php
namespace Yjv\HttpQueue\Connection;

use Yjv\HttpQueue\Payload\DestinationPayloadInterface;

use Yjv\HttpQueue\Payload\SourcePayloadInterface;

interface ConnectionHandleInterface
{
    public function getResource();
    public function setOptions(array $options);
    public function setOption($name, $value);
    public function getOptions();
    public function execute();
    public function getLastTransferInfo($option = null);
    public function close();
    public function setSourceStream(SourceStreamInterface $sourcePayload);
    public function setDestinationStream(DestinationStreamInterface $destinationPaylod);
    public function setSourcePayload(SourcePayloadInterface $sourcePayload);
    public function setDelegate();
}
