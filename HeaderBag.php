<?php
namespace Yjv\HttpQueue;

use Symfony\Component\HttpFoundation\HeaderBag as BaseHeaderBag;

abstract class AbstractHeaderBag extends BaseHeaderBag
{
    const COOKIES_HEADER           = 'header';
    const COOKIES_ARRAY            = 'array';
    
    /**
     * @var array
     */
    protected $cookies             = array();
    
    /**
     * @var array
    */
    protected $headerNames         = array();
    
    protected $syncing = false;
    
    /**
     * {@inheritdoc}
    */
    public function __toString()
    {
        ksort($this->headerNames);
        return parent::__toString();
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
    
    public function allPreserveCaseFlattened($includeCookieHeaders = true)
    {
        $headers = array();
    
        foreach ($this->allPreserveCase() as $name => $values) {
    
            if (
                !$includeCookieHeaders 
                && in_array(strtolower($name), array('set-cookie', 'cookie'))
            ) {
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
        $this->syncHeadersToCookies();
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
        $this->syncHeadersToCookies();
    }
    
    protected function syncCookiesToHeaders()
    {
        if (!$this->syncing) {
            
            $this->syncing = true;
            $this->doSyncCookiesToHeaders();
            $this->syncing = false;
        }
    }
    
    protected function syncHeadersToCookies()
    {
        if (!$this->syncing) {
            
            $this->syncing = true;
            $this->doSyncHeadersToCookies();
            $this->syncing = false;
        }
    }
    
    abstract protected function doSyncCookiesToHeaders();
    abstract protected function doSyncHeadersToCookies();
    abstract protected function getCookies($format = self::COOKIES_ARRAY);
}