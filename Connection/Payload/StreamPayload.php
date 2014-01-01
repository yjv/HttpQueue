<?php
namespace Yjv\HttpQueue\Connection\Payload;

use Yjv\HttpQueue\Stream\Stream;
use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

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
        return $this->read($lengthOfDataToRead);
    }
    
    public function getContentType()
    {
    }
    
    public function getContentLength()
    {
    }
    
    protected function attemptRewind()
    {
        if ($this->isSeekable()) {
            
            $this->rewind();
        }
    }
}
