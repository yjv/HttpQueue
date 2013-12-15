<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Connection\SourcePayloadInterface;

use Yjv\HttpQueue\Url\Query;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class FormFieldsPayload extends Query implements SourcePayloadInterface
{
    public function setHandle(ConnectionHandleInterface $handle)
    {
        return $this;
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
