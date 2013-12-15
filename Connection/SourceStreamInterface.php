<?php
namespace Yjv\HttpQueue\Connection;

interface SourceStreamInterface extends PayloadInterface
{
    /**
     * 
     * @param int $lengthOfDataToRead
     */
    public function readStream($lengthOfDataToRead);
}
