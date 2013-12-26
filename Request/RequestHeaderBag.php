<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\AbstractHeaderBag;

use Symfony\Component\HttpFoundation\Cookie;

use Yjv\HttpQueue\Cookie\Factory as CookieFactory;

class RequestHeaderBag extends AbstractHeaderBag
{
    /**
     * Sets a cookie.
     *
     * @param Cookie $cookie
     *
     * @api
     */
    public function setCookie(Cookie $cookie)
    {
        $this->cookies[$cookie->getName()] = $cookie;
        $this->syncCookiesToHeaders();
        return $this;
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
    public function removeCookie($name)
    {
        unset($this->cookies[$name]);
        $this->syncCookiesToHeaders();
        return $this;
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
        if (!in_array($format, array(self::COOKIES_HEADER, self::COOKIES_ARRAY))) {
            throw new \InvalidArgumentException(sprintf('Format "%s" invalid (%s).', $format, implode(', ', array(self::COOKIES_HEADER, self::COOKIES_ARRAY))));
        }

        if (self::COOKIES_ARRAY === $format) {
            return $this->cookies;
        }
        
        return implode('; ', array_map(function(Cookie $cookie)
        {
            return sprintf('%s=%s', $cookie->getName(), $cookie->getValue());
        }, $this->cookies));
    }

    protected function doSyncCookiesToHeaders()
    {
        if (empty($this->cookies)) {
            
            $this->remove('Cookie');
            return;
        }
        
        $this->set('Cookie', $this->getCookies(self::COOKIES_HEADER), true);
     }

     protected function doSyncHeadersToCookies()
     {
         $this->cookies = array();
         
         if (!$this->has('Cookie')) {
             
             return;
         }
         
         foreach (CookieFactory::createMultipleFromCookieHeader($this->get('Cookie', null, true)) as $cookie) {
             
             $this->setCookie($cookie);
         }
     }
}