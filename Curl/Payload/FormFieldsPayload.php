<?php
namespace Yjv\HttpQueue\Curl\Payload;

use Yjv\HttpQueue\Curl\CurlHandle;
use Yjv\HttpQueue\Curl\CurlFile;
use Yjv\HttpQueue\Connection\Payload\SourcePayloadInterface;
use Yjv\HttpQueue\Url\Query;
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
        return array_map(array($this, 'replaceCurlFiles'), $this->getSingleLevelArray());
    }
    
    public function getContentType()
    {
    }
    
    public function getContentLength()
    {
    }

    protected function replaceCurlFiles($element)
    {
        if ($element instanceof CurlFile) {

            return $element->getCurlValue();
        }

        return $element;
    }
}
