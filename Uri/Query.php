<?php
namespace Yjv\HttpQueue\Uri;

class Query extends \ArrayObject
{
    protected $literalIntegerIndexes = false;
    
    public static function createFromString($queryString)
    {
        parse_str($queryString, $queryParams);
        return new static($queryParams);
    }
    
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
    
    public function getParameterizedArray($urlEncoded = true, $prefix = null, $startingData = null)
    {
        $elements = array();
        
        foreach ($startingData ?: $this as $key => $value) {
            
            if ($prefix && !$this->literalIntegerIndexes && is_int($key)) {
                
                $key = '';
            }
            
            $key = $urlEncoded ? rawurlencode($key) : $key;
            
            if ($prefix) {

                $key = $prefix . '[' . $key . ']';
            }
            
            if (is_array($value)) {
                
                foreach ($this->getParameterizedArray($urlEncoded, $key, $value) as $subValue) {

                    $elements[] = $subValue;
                }
            } else {
                
                $elements[] = sprintf('%s=%s', $key, $urlEncoded ? rawurlencode((string)$value) : (string)$value);
            }
        }
        
        return $elements;
    }
    
    public function getString($prefix = null)
    {
        return implode('&', $this->getParameterizedArray(true, $prefix));
    }
}
