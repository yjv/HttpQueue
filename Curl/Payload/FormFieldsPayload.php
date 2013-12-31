<?php
namespace Yjv\HttpQueue\Curl\Payload;

use Yjv\HttpQueue\Curl\CurlFileInterface;
use Yjv\HttpQueue\Curl\CurlHandle;
use Yjv\HttpQueue\Connection\Payload\SourcePayloadInterface;
use Yjv\HttpQueue\Uri\Query;
use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class FormFieldsPayload extends Query implements SourcePayloadInterface
{
    public function setHandle(ConnectionHandleInterface $handle)
    {
        if (!$handle instanceof CurlHandle) {
            
            throw new \InvalidArgumentException('$handle must be an instance of Yjv\HttpQueue\Curl\CurlHandle');
        }
        
        $handle->setOption(CURLOPT_POST, true);
        
        return $this;
    }

    public function getPayloadData()
    {
        return array_map('reset', $this->getSingleLevelArray());
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
