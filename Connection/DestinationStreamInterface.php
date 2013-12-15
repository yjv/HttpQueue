<?php
namespace Yjv\HttpQueue\Payload;

interface DestinationStreamInterface
{
    /**
     * 
     * @param string $data
     */
    public function writeStream($data);
}
