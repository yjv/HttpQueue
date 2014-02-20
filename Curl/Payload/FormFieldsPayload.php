<?php
namespace Yjv\HttpQueue\Curl\Payload;

use Yjv\HttpQueue\Curl\CurlFileInterface;
use Yjv\HttpQueue\Curl\CurlHandle;
use Yjv\HttpQueue\Transport\Payload\PayloadSourceInterface;
use Yjv\HttpQueue\Uri\Query;
use Yjv\HttpQueue\Transport\HandleInterface;

class FormFieldsPayload extends Query implements PayloadSourceInterface
{
    public function setDestinationHandle(HandleInterface $handle)
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
