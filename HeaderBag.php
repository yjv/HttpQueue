<?php
namespace Yjv\HttpQueue;

use Symfony\Component\HttpFoundation\HeaderBag as BaseHeaderBag;

class HeaderBag extends BaseHeaderBag
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
    
    /**
     * Returns an array with all cookies
     *
     * @param string $format
     *
     * @throws \InvalidArgumentException When the $format is invalid
     *
     * @return array
     *
     * @api
     */
    public function getCookies($format = self::COOKIES_ARRAY)
    {
        if (!in_array($format, array(self::COOKIES_HEADER, self::COOKIES_ARRAY, self::COOKIES_FLAT_ARRAY))) {
            throw new \InvalidArgumentException(sprintf('Format "%s" invalid (%s).', $format, implode(', ', array(self::COOKIES_HEADER, self::COOKIES_ARRAY, self::COOKIES_FLAT_ARRAY))));
        }
    
        if (self::COOKIES_ARRAY === $format) {
            return $this->cookies;
        }
    
        return implode('; ', array_map(function(Cookie $cookie)
        {
            return sprintf('%s=%s', $cookie->getName(), $cookie->getValue());
        }, $this->cookies));
    }
    
    abstract protected function syncCookiesToHeaders();
    abstract protected function syncHeadersToCookies();
    abstract protected function getCookies($format = self::COOKIES_ARRAY);
}