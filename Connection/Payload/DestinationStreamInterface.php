<?php
namespace Yjv\HttpQueue\Connection\Payload;

interface DestinationStreamInterface extends DestinationPayloadInterface
{
    /**
     * 
     * @param string $data
     */
    public function writeStream($data);
}
