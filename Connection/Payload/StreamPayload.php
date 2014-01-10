<?php
namespace Yjv\HttpQueue\Connection\Payload;

use Yjv\HttpQueue\Stream\Stream;
use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class StreamPayload extends Stream implements DestinationStreamInterface, SourceStreamInterface
{
    protected $contentType;
    protected $contentLength;
    
    public function setDestinationHandle(ConnectionHandleInterface $handle)
    {
        $this->attemptRewind();
        return $this;
    }
    
    public function setSourceHandle(ConnectionHandleInterface $handle)
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
        return $this->contentType;
    }
    
    public function getContentLength()
    {
        return $this->contentLength;
    }
    
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }
    
    public function setContentLength($contentLength)
    {
        $this->contentLength = $contentLength;
        return $this;
    }
    
    protected function attemptRewind()
    {
        if ($this->isSeekable()) {
            
            $this->rewind();
        }
    }
}
