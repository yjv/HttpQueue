<?php
namespace Yjv\HttpQueue\Transport\Payload;

interface StreamSourceInterface extends PayloadSourceInterface
{
    /**
     * 
     * @param int $lengthOfDataToRead
     */
    public function readStream($lengthOfDataToRead);
}
