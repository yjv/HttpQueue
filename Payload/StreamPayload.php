<?php
namespace Yjv\HttpQueue\Payload;

use Yjv\HttpQueue\Connection\Payload\SourceStreamInterface;

use Yjv\HttpQueue\Connection\Payload\DestinationStreamInterface;

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
    
    public function getPayloadData()
    {
        return $this->__toString();
    }
    
    public function setPayloadData($data)
    {
        if($this->truncate(0))
        {
            return $this->write($data);
        }
        
        return true;
    }
    
    public function getContentType()
    {
    }
    
    public function getContentLength()
    {
    }
    
    public function truncate($size)
    {
        if ($this->isSeekable() && $this->isWritable()) {
            
            return ftruncate($this->stream, $size);
        }
        
        return false;
    }
    
    protected function attemptRewind()
    {
        if ($this->isSeekable()) {
            
            $this->rewind();
        }
    }
}
