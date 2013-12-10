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
        return $this->buildString(iterator_to_array($this));
    }
    
    protected function buildString(array $params, $prefix = null)
    {
        $elements = array();
        
        foreach ($params as $key => $value) {
            
            $key = rawurlencode($key);
            $key = ($prefix)
                ? $prefix.'['.$key.']'
                : $key
            ;
            
            if (is_array($value)) {
                
                $elements[] = $this->buildString($value, $key);
            } else {
                $elements[] = sprintf(
                    '%s=%s', 
                    $key, 
                    $value ? rawurlencode($value) : ''
                );
            }
        }
        
        return implode('&', $elements);
    }
}
