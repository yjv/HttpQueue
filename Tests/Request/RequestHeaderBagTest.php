<?php
namespace Yjv\HttpQueue\Tests\Request;

use Symfony\Component\HttpFoundation\Cookie;

use Yjv\HttpQueue\Request\RequestHeaderBag;

use Yjv\HttpQueue\Tests\AbstractHeaderBadTest;

class RequestHeaderBagTest extends AbstractHeaderBadTest
{
    protected function createHeaderBag(array $headers = array())
    {
        return new RequestHeaderBag($headers);
    }    
    
    public function testToStringIncludesCookieHeader()
    {
        $bag = $this->createHeaderBag();
        $bag->setCookie(new Cookie('foo', 'bar'));
        $bag->setCookie(new Cookie('foo2', 'bar2'));
    
        $this->assertContains("Cookie: foo=bar; foo2=bar2", explode("\r\n", $bag->__toString()));
    
        $bag->removeCookie('foo');
    
        $this->assertNotContains("Cookie: foo=bar", explode("\r\n", $bag->__toString()));
    }
}
