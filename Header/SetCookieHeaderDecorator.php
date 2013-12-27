<?php
namespace Yjv\HttpQueue\Header;

use Symfony\Component\HttpFoundation\Cookie;

use Yjv\HttpQueue\Cookie\Factory;

class SetCookieHeaderDecorator implements HeaderDecoratorInterface
{
    protected $cookie;
    
    public function __construct($setCookieHeader)
    {
        $this->cookie = Factory::createFromSetCookieHeader($setCookieHeader);
    }
    
    public function __toString()
    {
        return (string)$this->cookie;
    }
    
    public function getCookie()
    {
        return $this->cookie;
    }
    
    public function setCookie(Cookie $cookie)
    {
        $this->cookie = $cookie;
        return $this;
    }
}
