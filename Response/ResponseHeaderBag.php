<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yjv\HttpQueue\Response;

use Yjv\HttpQueue\AbstractHeaderBag;

use Symfony\Component\HttpFoundation\Cookie;

use Yjv\HttpQueue\Cookie\Factory as CookieFactory;

class ResponseHeaderBag extends AbstractHeaderBag
{
    const COOKIES_FLAT_ARRAY       = 'flat_array';

    /**
     * @var array
     */
    protected $computedCacheControl = array();
    
    /**
     * Constructor.
     *
     * @param array $headers An array of HTTP headers
     *
     * @api
     */
    public function __construct(array $headers = array())
    {
        parent::__construct($headers);

        if (!isset($this->headers['cache-control'])) {
            $this->set('Cache-Control', '');
        }
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
        
        // ensure the cache-control header has sensible defaults
        if (in_array($uniqueKey, array('cache-control', 'etag', 'last-modified', 'expires'))) {
            $computed = $this->computeCacheControlValue();
            $this->headers['cache-control'] = array($computed);
            $this->headerNames['cache-control'] = 'Cache-Control';
            $this->computedCacheControl = $this->parseCacheControl($computed);
        }
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
        
        if ('cache-control' === $uniqueKey) {
            $this->computedCacheControl = array();
        }
    }
    
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function replace(array $headers = array())
    {
        parent::replace($headers);

        if (!isset($this->headers['cache-control'])) {
            $this->set('Cache-Control', '');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheControlDirective($key)
    {
        return array_key_exists($key, $this->computedCacheControl);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCacheControlDirective($key)
    {
        return array_key_exists($key, $this->computedCacheControl) ? $this->computedCacheControl[$key] : null;
    }
        
    /**
     * Sets a cookie.
     *
     * @param Cookie $cookie
     *
     * @api
     */
    public function setCookie(Cookie $cookie)
    {
        $this->cookies[$cookie->getDomain()][$cookie->getPath()][$cookie->getName()] = $cookie;
        $this->syncCookiesToHeaders();
    }
        
    /**
     * Removes a cookie from the array, but does not unset it in the browser
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     *
     * @api
     */
    public function removeCookie($name, $path = '/', $domain = null)
    {
        if (null === $path) {
            $path = '/';
        }

        unset($this->cookies[$domain][$path][$name]);

        if (empty($this->cookies[$domain][$path])) {
            unset($this->cookies[$domain][$path]);

            if (empty($this->cookies[$domain])) {
                unset($this->cookies[$domain]);
            }
        }
        $this->syncCookiesToHeaders();
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
        
        if (self::COOKIES_FLAT_ARRAY) {

            $flattenedCookies = array();
            foreach ($this->cookies as $path) {
                foreach ($path as $cookies) {
                    foreach ($cookies as $cookie) {
                        $flattenedCookies[] = $cookie;
                    }
                }
            }
            
            return $flattenedCookies;
        }

        return implode('; ', array_map(function(Cookie $cookie)
        {
            return (string)$cookie;
        }, $this->getCookies(self::COOKIES_FLAT_ARRAY)));
    }

    protected function doSyncCookiesToHeaders()
    {
        if (empty($this->cookies)) {

            $this->remove('Set-Cookie');
            return;
        }

        $this->set('Set-Cookie', $this->getCookies(self::COOKIES_HEADER), true);
    }

    protected function doSyncHeadersToCookies()
    {
        $this->cookies = array();
        
        foreach ($this->get('Set-Cookie', null, false) as $header) {

            $this->setCookie(CookieFactory::createFromSetCookieHeader($header));
        }
    }

    /**
     * Returns the calculated value of the cache-control header.
     *
     * This considers several other headers and calculates or modifies the
     * cache-control header to a sensible, conservative value.
     *
     * @return string
     */
    protected function computeCacheControlValue()
    {
        if (!$this->cacheControl && !$this->has('ETag') && !$this->has('Last-Modified') && !$this->has('Expires')) {
            return 'no-cache';
        }
    
        if (!$this->cacheControl) {
            // conservative by default
            return 'private, must-revalidate';
        }
    
        $header = $this->getCacheControlHeader();
        if (isset($this->cacheControl['public']) || isset($this->cacheControl['private'])) {
            return $header;
        }
    
        // public if s-maxage is defined, private otherwise
        if (!isset($this->cacheControl['s-maxage'])) {
            return $header.', private';
        }
    
        return $header;
    }
}
