<?php
namespace Yjv\HttpQueue\Payload;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

use Guzzle\Stream\Stream;

class StreamPayload extends Stream implements PayloadInterface
{
    protected $sourceHandle;
    protected $destinationHandle;
    
    public function attachSourceHandle(CurlHandleInterface $handle)
    {
        $this->sourceHandle = $handle;
        $this->sourceHandle->setOption(CURLOPT_READFUNCTION, array($this, 'readPayload'));
        $this->rewind();
        return $this;
    }
    
    public function attachDestinationHandle(CurlHandleInterface $handle)
    {
        $this->destinationHandle = $handle;
        $this->destinationHandle->setOption(CURLOPT_WRITEFUNCTION, array($this, 'writePayload'));
        $this->rewind();
        return $this;
    }
    
    /**
     *
     * @see RequestMediatorInterface::writeResponseBody
     */
    public function writePayload(CurlHandleInterface $handle, $data)
    {
        return $this->write($data);
    }
    
    /**
     *
     * @see RequestMediatorInterface::readRequestBody
     */
    public function readPayload(CurlHandleInterface $handle, $fileDescriptor, $lengthOfDataRead)
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
