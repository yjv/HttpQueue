<?php
namespace Yjv\HttpQueue\Connection\Payload;

interface DestinationPayloadInterface extends PayloadInterface
{
    public function setPayloadData($data);
}
