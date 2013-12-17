<?php
namespace Yjv\HttpQueue\Payload;

use Yjv\HttpQueue\Connection\SourceStreamInterface;

use Yjv\HttpQueue\Connection\DestinationStreamInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Guzzle\Stream\Stream;

class StreamPayload extends Stream implements DestinationStreamInterface, SourceStreamInterface
{
    public function setHandle(ConnectionHandleInterface $handle)
    {
        $this->attemptRewind();
        return $this;
    }
    
    /**
     *
     * @see RequestMediatorInterface::writeResponseBody
     */
    public function writeStream($data)
    {
        return $this->write($data);
    }
    
    /**
     *
     * @see RequestMediatorInterface::readRequestBody
     */
    public function readStream($lengthOfDataToRead)
    {
        return $this->read($lengthOfDataRead);
    }
    
    public function getContentType()
    {
        
    }
    
    protected function attemptRewind()
    {
        if ($this->isSeekable()) {
            
            $this->rewind();
        }
    }
}
