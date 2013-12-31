<?php
namespace Yjv\HttpQueue\Uri;

class Query extends \ArrayObject
{
    protected $literalIntegerIndexes = false;
    
    public function __construct(array $queryArray = array())
    {
        parent::__construct($queryArray);
    }
    
    public function __toString()
    {
        return $this->getString();
    }
    
    public function setLiteralIntegerIndexes($literalIntegerIndexes)
    {
        $this->literalIntegerIndexes = $literalIntegerIndexes;
        return $this;
    }
    
    public function getLiteralIntegerIndexes()
    {
        return $this->literalIntegerIndexes;
    }
    
    public function getSingleLevelArray($literalIntegerIndexes = false, $prefix = null, $startingData = null)
    {
        $elements = array();
        
        foreach ($startingData ?: $this as $key => $value) {
            
            if ($prefix && !$literalIntegerIndexes && is_int($key)) {
                
                $key = '';
            }
            
            $key = rawurlencode($key);
            
            if ($prefix) {

                $key = sprintf('%s[%s]', $prefix, $key);
            }
            
            if (is_array($value)) {
                
                foreach ($this->getSingleLevelArray($literalIntegerIndexes, $key, $value) as $subKey => $subValue) {

                    if (!isset($elements[$subKey])) {
                        
                        $elements[$subKey] = array();
                    }
                    
                    $elements[$subKey] = array_merge($elements[$subKey], $subValue);
                }
            } else {

                if (!isset($elements[$key])) {
                    
                    $elements[$key] = array();
                }
                
                $elements[$key] = array_merge($elements[$key], array($this->processValueForOutput($value)));
            }
        }
        
        return $elements;
    }
    
    public function getParameterizedArray($literalIntegerIndexes = false, $prefix = null)
    {
        $elements = array();
        
        foreach ($this->getSingleLevelArray($literalIntegerIndexes, $prefix) as $key => $value) {
            
            foreach ($value as $subValue) {

                $elements[] = sprintf('%s=%s', $key, $subValue);
            }
        }
        
        return $elements;
    }
    
    public function getString($literalIntegerIndexes = false, $prefix = null)
    {
        return implode('&', $this->getParameterizedArray($literalIntegerIndexes, $prefix));
    }
    
    protected function processValueForOutput($value)
    {
        return rawurlencode((string)$value);
    }
}
