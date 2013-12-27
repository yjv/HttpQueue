<?php
namespace Yjv\HttpQueue\Header;

use Symfony\Component\HttpFoundation\HeaderBag as BaseHeaderBag;

class HeaderBag extends BaseHeaderBag
{
    /**
     * @var array
     */
    protected $headerNames         = array();
    
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return implode("\r\n", $this->allPreserveCaseFlattened());
    }
    
    /**
     * Returns the headers, with original capitalizations.
     *
     * @return array An array of headers
     */
    public function allPreserveCase()
    {
        return array_combine($this->headerNames, $this->headers);
    }
    
    public function allPreserveCaseFlattened(array $headerExclusions = array())
    {
        $headers = array();
        $headerExclusions = array_map('strtolower', $headerExclusions);
        
        foreach ($this->allPreserveCase() as $name => $values) {
    
            if (in_array(strtolower($name), $headerExclusions)) {
                
                continue;
            }
            
            foreach ($values as $value) {
    
                $headers[] = sprintf('%s: %s', $name, $value);
            }
        }
    
        return $headers;
    }
    
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function replace(array $headers = array())
    {
        $this->headerNames = array();
        parent::replace($headers);
    }
    
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function set($key, $values, $replace = true)
    {
        parent::set($key, $values, $replace);
        $uniqueKey = strtr(strtolower($key), '_', '-');
        $this->headerNames[$uniqueKey] = $key;
    }
    
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function remove($key)
    {
        parent::remove($key);
        $uniqueKey = strtr(strtolower($key), '_', '-');
        unset($this->headerNames[$uniqueKey]);
    }
}