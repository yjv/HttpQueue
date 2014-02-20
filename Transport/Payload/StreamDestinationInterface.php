<?php
namespace Yjv\HttpQueue\Transport\Payload;

interface StreamDestinationInterface extends PayloadDestinationInterface
{
    /**
     * 
     * @param string $data
     */
    public function writeStream($data);
}
