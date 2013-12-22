<?php
namespace Yjv\HttpQueue\Connection\Payload;

interface SourceStreamInterface extends SourcePayloadInterface
{
    /**
     * 
     * @param int $lengthOfDataToRead
     */
    public function readStream($lengthOfDataToRead);
}
