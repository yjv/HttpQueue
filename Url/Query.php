<?php
namespace Yjv\HttpQueue\Url;

class Query extends \ArrayObject
{
    public static function createFromString($queryString)
    {
        parse_str($queryString, $queryParams);
        return new static($queryParams);
    }
    
    public function __toString()
    {
        return $this->getString();
    }
    
    public function getSingleLevelArray($urlEncoded = true, $prefix = null)
    {
        $elements = array();
        
        foreach ($this as $key => $value) {
            
            $key = $urlEncoded ? rawurlencode($key) : $key;
            $key = ($prefix)
                ? $prefix.'['.$key.']'
                : $key
            ;
            
            if (is_array($value)) {
                
                foreach ($this->getSingleLevelArray($urlEncoded, $key) as $subKey => $subValue) {

                    $elements[$subKey] = $subValue;
                }
            } else {
                
                $elements[$key] = $value ? (string)($urlEncoded ? rawurlencode($value) : $value) : '';
            }
        }
        
        return $elements;
    }
    
    public function getString($prefix = null)
    {
        $elements = array();
        
        foreach ($this->getSingleLevelArray(true, $prefix) as $value) {
            
            $elements[] = $value;
        }
        
        return implode('&', $elements);
    }
}
