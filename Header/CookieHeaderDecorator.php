<?php
namespace Yjv\HttpQueue\Header;

use Symfony\Component\HttpFoundation\Cookie;

use Yjv\HttpQueue\Cookie\Factory;

class CookieHeaderDecorator implements HeaderDecoratorInterface
{
    protected $cookies;
    
    public function __construct($cookieHeader)
    {
        $this->cookies = Factory::createMultipleFromCookieHeader($this->cookieHeader);
    }
    
    public function __toString()
    {
        return implode('; ', array_map(function(Cookie $cookie)
        {
            return sprintf('%s=%s', $cookie->getName(), $cookie->getValue());    
        }, $this->cookies));
    }
    
    public function getCookies()
    {
        return $this->cookies;
    }
    
    public function addCookie(Cookie $cookie)
    {
        $this->cookies[$cookie->getName()] = $cookie->getValue();
        return $this;
    }
    
    public function removeCookie($name)
    {
        unset($this->cookies[$name]);
        return $this;
    }
}
