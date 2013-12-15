<?php
namespace Yjv\HttpQueue\Payload;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

use Guzzle\Stream\Stream;

class StreamPayload extends Stream implements StreamDestinationPayloadInterface, StreamSourcePayloadInterface
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
    public function writePayload($data)
    {
        return $this->write($data);
    }
    
    /**
     *
     * @see RequestMediatorInterface::readRequestBody
     */
    public function readPayload($lengthOfDataToRead)
    {
        return $this->read($lengthOfDataRead);
    }
    
    protected function attemptRewind()
    {
        if ($this->isSeekable()) {
            
            $this->rewind();
        }
    }
}
