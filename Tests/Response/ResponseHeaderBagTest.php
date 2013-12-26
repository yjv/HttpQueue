<?php
namespace Yjv\HttpQueue\Tests\Response;

use Yjv\HttpQueue\AbstractHeaderBag;

use Symfony\Component\HttpFoundation\Cookie;

use Yjv\HttpQueue\Response\ResponseHeaderBag;

use Yjv\HttpQueue\Request\RequestHeaderBag;

use Yjv\HttpQueue\Tests\AbstractHeaderBadTest;

class ResponseHeaderBagTest extends AbstractHeaderBadTest
{
    protected function createHeaderBag(array $headers = array())
    {
        return new ResponseHeaderBag($headers);
    }

    public function provideAllPreserveCase()
    {
        return array(
                array(
                        array('fOo' => 'BAR'),
                        array('fOo' => array('BAR'), 'Cache-Control' => array('no-cache'))
                ),
                array(
                        array('ETag' => 'xyzzy'),
                        array('ETag' => array('xyzzy'), 'Cache-Control' => array('private, must-revalidate'))
                ),
                array(
                        array('Content-MD5' => 'Q2hlY2sgSW50ZWdyaXR5IQ=='),
                        array('Content-MD5' => array('Q2hlY2sgSW50ZWdyaXR5IQ=='), 'Cache-Control' => array('no-cache'))
                ),
                array(
                        array('P3P' => 'CP="CAO PSA OUR"'),
                        array('P3P' => array('CP="CAO PSA OUR"'), 'Cache-Control' => array('no-cache'))
                ),
                array(
                        array('WWW-Authenticate' => 'Basic realm="WallyWorld"'),
                        array('WWW-Authenticate' => array('Basic realm="WallyWorld"'), 'Cache-Control' => array('no-cache'))
                ),
                array(
                        array('X-UA-Compatible' => 'IE=edge,chrome=1'),
                        array('X-UA-Compatible' => array('IE=edge,chrome=1'), 'Cache-Control' => array('no-cache'))
                ),
                array(
                        array('X-XSS-Protection' => '1; mode=block'),
                        array('X-XSS-Protection' => array('1; mode=block'), 'Cache-Control' => array('no-cache'))
                ),
        );
    }
    

    public function testCacheControlHeader()
    {
        $bag = $this->createHeaderBag(array());
        $this->assertEquals('no-cache', $bag->get('Cache-Control'));
        $this->assertTrue($bag->hasCacheControlDirective('no-cache'));
    
        $bag = $this->createHeaderBag(array('Cache-Control' => 'public'));
        $this->assertEquals('public', $bag->get('Cache-Control'));
        $this->assertTrue($bag->hasCacheControlDirective('public'));
    
        $bag = $this->createHeaderBag(array('ETag' => 'abcde'));
        $this->assertEquals('private, must-revalidate', $bag->get('Cache-Control'));
        $this->assertTrue($bag->hasCacheControlDirective('private'));
        $this->assertTrue($bag->hasCacheControlDirective('must-revalidate'));
        $this->assertFalse($bag->hasCacheControlDirective('max-age'));
    
        $bag = $this->createHeaderBag(array('Expires' => 'Wed, 16 Feb 2011 14:17:43 GMT'));
        $this->assertEquals('private, must-revalidate', $bag->get('Cache-Control'));
    
        $bag = $this->createHeaderBag(array(
                'Expires' => 'Wed, 16 Feb 2011 14:17:43 GMT',
                'Cache-Control' => 'max-age=3600'
        ));
        $this->assertEquals('max-age=3600, private', $bag->get('Cache-Control'));
    
        $bag = $this->createHeaderBag(array('Last-Modified' => 'abcde'));
        $this->assertEquals('private, must-revalidate', $bag->get('Cache-Control'));
    
        $bag = $this->createHeaderBag(array('Etag' => 'abcde', 'Last-Modified' => 'abcde'));
        $this->assertEquals('private, must-revalidate', $bag->get('Cache-Control'));
    
        $bag = $this->createHeaderBag(array('cache-control' => 'max-age=100'));
        $this->assertEquals('max-age=100, private', $bag->get('Cache-Control'));
    
        $bag = $this->createHeaderBag(array('cache-control' => 's-maxage=100'));
        $this->assertEquals('s-maxage=100', $bag->get('Cache-Control'));
    
        $bag = $this->createHeaderBag(array('cache-control' => 'private, max-age=100'));
        $this->assertEquals('max-age=100, private', $bag->get('Cache-Control'));
    
        $bag = $this->createHeaderBag(array('cache-control' => 'public, max-age=100'));
        $this->assertEquals('max-age=100, public', $bag->get('Cache-Control'));
    
        $bag = $this->createHeaderBag();
        $bag->set('Last-Modified', 'abcde');
        $this->assertEquals('private, must-revalidate', $bag->get('Cache-Control'));
    }
    
    public function testToStringIncludesCookieHeaders()
    {
        $bag = $this->createHeaderBag();
        $bag->setCookie(new Cookie('foo', 'bar'));
    
        $this->assertContains("Set-Cookie: foo=bar; path=/; httponly", explode("\r\n", $bag->__toString()));
    
        $bag->clearCookie('foo');
    
        $this->assertContains("Set-Cookie: foo=deleted; expires=".gmdate("D, d-M-Y H:i:s T", time() - 31536001)."; path=/; httponly", explode("\r\n", $bag->__toString()));
    }

    public function testReplace()
    {
        $bag = $this->createHeaderBag();
        $this->assertEquals('no-cache', $bag->get('Cache-Control'));
        $this->assertTrue($bag->hasCacheControlDirective('no-cache'));
    
        $bag->replace(array('Cache-Control' => 'public'));
        $this->assertEquals('public', $bag->get('Cache-Control'));
        $this->assertTrue($bag->hasCacheControlDirective('public'));
    }
    
    public function testReplaceWithRemove()
    {
        $bag = $this->createHeaderBag();
        $this->assertEquals('no-cache', $bag->get('Cache-Control'));
        $this->assertTrue($bag->hasCacheControlDirective('no-cache'));
    
        $bag->remove('Cache-Control');
        $bag->replace(array());
        $this->assertEquals('no-cache', $bag->get('Cache-Control'));
        $this->assertTrue($bag->hasCacheControlDirective('no-cache'));
    }    
    
    public function testCookiesWithSameNames()
    {
        $bag = $this->createHeaderBag();
        $bag->setCookie(new Cookie('foo', 'bar', 0, '/path/foo', 'foo.bar'));
        $bag->setCookie(new Cookie('foo', 'bar', 0, '/path/bar', 'foo.bar'));
        $bag->setCookie(new Cookie('foo', 'bar', 0, '/path/bar', 'bar.foo'));
        $bag->setCookie(new Cookie('foo', 'bar'));
    
        $this->assertCount(4, $bag->getCookies());
    
        $headers = explode("\r\n", $bag->__toString());
        $this->assertContains("Set-Cookie: foo=bar; path=/path/foo; domain=foo.bar; httponly", $headers);
        $this->assertContains("Set-Cookie: foo=bar; path=/path/foo; domain=foo.bar; httponly", $headers);
        $this->assertContains("Set-Cookie: foo=bar; path=/path/bar; domain=bar.foo; httponly", $headers);
        $this->assertContains("Set-Cookie: foo=bar; path=/; httponly", $headers);
    
        $cookies = $bag->getCookies(AbstractHeaderBag::COOKIES_ARRAY);
        $this->assertTrue(isset($cookies['foo.bar']['/path/foo']['foo']));
        $this->assertTrue(isset($cookies['foo.bar']['/path/bar']['foo']));
        $this->assertTrue(isset($cookies['bar.foo']['/path/bar']['foo']));
        $this->assertTrue(isset($cookies['']['/']['foo']));
    }
    
    public function testRemoveCookie()
    {
        $bag = $this->createHeaderBag();
        $bag->setCookie(new Cookie('foo', 'bar', 0, '/path/foo', 'foo.bar'));
        $bag->setCookie(new Cookie('bar', 'foo', 0, '/path/bar', 'foo.bar'));
    
        $cookies = $bag->getCookies(AbstractHeaderBag::COOKIES_ARRAY);
        $this->assertTrue(isset($cookies['foo.bar']['/path/foo']));
    
        $bag->removeCookie('foo', '/path/foo', 'foo.bar');
    
        $cookies = $bag->getCookies(AbstractHeaderBag::COOKIES_ARRAY);
        $this->assertFalse(isset($cookies['foo.bar']['/path/foo']));
    
        $bag->removeCookie('bar', '/path/bar', 'foo.bar');
    
        $cookies = $bag->getCookies(AbstractHeaderBag::COOKIES_ARRAY);
        $this->assertFalse(isset($cookies['foo.bar']));
    }
    
    public function testRemoveCookieWithNullRemove()
    {
        $bag = $this->createHeaderBag();
        $bag->setCookie(new Cookie('foo', 'bar', 0));
        $bag->setCookie(new Cookie('bar', 'foo', 0));
    
        $cookies = $bag->getCookies(AbstractHeaderBag::COOKIES_ARRAY);
        $this->assertTrue(isset($cookies['']['/']));
    
        $bag->removeCookie('foo', null);
        $cookies = $bag->getCookies(AbstractHeaderBag::COOKIES_ARRAY);
        $this->assertFalse(isset($cookies['']['/']['foo']));
    
        $bag->removeCookie('bar', null);
        $cookies = $bag->getCookies(AbstractHeaderBag::COOKIES_ARRAY);
        $this->assertFalse(isset($cookies['']['/']['bar']));
    }
}
