<?php
namespace Yjv\HttpQueue\Curl\Payload;

use Yjv\HttpQueue\Curl\CurlFileInterface;
use Yjv\HttpQueue\Curl\CurlHandle;
use Yjv\HttpQueue\Connection\Payload\SourcePayloadInterface;
use Yjv\HttpQueue\Uri\Query;
use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class FormFieldsPayload extends Query implements SourcePayloadInterface
{
    public function setDestinationHandle(ConnectionHandleInterface $handle)
    {
        if (!$handle instanceof CurlHandle) {
            
            throw new \InvalidArgumentException('$handle must be an instance of Yjv\HttpQueue\Curl\CurlHandle');
        }
        
        $handle->setOptions(array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array_map('reset', $this->getSingleLevelArray())
        ));
        
        return $this;
    }
    
    public function getContentType()
    {
    }
    
    public function getContentLength()
    {
    }

    protected function processValueForOutput($value)
    {
        if ($value instanceof CurlFileInterface) {

            return $value->getCurlValue();
        }
        
        return parent::processValueForOutput($value);
    }
}
