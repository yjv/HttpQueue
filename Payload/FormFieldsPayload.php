<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Curl\CurlFile;

use Yjv\HttpQueue\Url\Query;

use Yjv\HttpQueue\Curl\CurlHandle;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

use Yjv\HttpQueue\Payload\SourcePayloadInterface;

class FormFieldsPayload extends Query implements SourcePayloadInterface
{
    protected $sourceHandle;

    public function setHandle(ConnectionHandleInterface $handle)
    {
        $this->sourceHandle = $handle;
        
        if (!$handle instanceof CurlHandle) {
            
            throw new InvalidArgumentException('The conection handle needs to be an instance of Yjv\HttpQueue\Curl\CurlHandle');
        }
    }
    
    public function getPayloadContent()
    {
        return array_map(array($this, 'replaceCurlFiles'), $this->getSingleLevelArray());
    }
    
    protected function replaceCurlFiles($element)
    {
        if ($element instanceof CurlFile) {
            
            return $element->getCurlValue();
        }
        
        return $element;
    }
}
