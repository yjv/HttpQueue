<?php
namespace Yjv\HttpQueue\Payload;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Connection\ConnectionInterface;

interface SourceStreamInterface
{
    /**
     * 
     * @param int $lengthOfDataToRead
     */
    public function readStream($lengthOfDataToRead);
}
