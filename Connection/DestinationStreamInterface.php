<?php
namespace Yjv\HttpQueue\Connection;

interface DestinationStreamInterface extends PayloadInterface
{
    /**
     * 
     * @param string $data
     */
    public function writeStream($data);
}
