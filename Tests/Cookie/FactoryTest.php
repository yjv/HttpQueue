<?php
namespace Yjv\HttpQueue\Cookie;

use Symfony\Component\HttpFoundation\Cookie;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFromSetCookieHeader()
    {
        $this->assertEquals(
            new Cookie('name', 'value', 0, '/', null, false, false), 
            Factory::createFromSetCookieHeader('name=value')
        );
        $this->assertEquals(
            new Cookie('name', 'value', strtotime('Tue, 31-Dec-2013 23:44:14 GMT'), '/', null, false, false), 
            Factory::createFromSetCookieHeader('name=value; expires=Tue, 31-Dec-2013 23:44:14 GMT')
        );
        $this->assertEquals(
            new Cookie('name', 'value', strtotime('Tue, 31-Dec-2013 23:44:14 GMT'), '/', '.yjv.com', false, false), 
            Factory::createFromSetCookieHeader('name=value; expires=Tue, 31-Dec-2013 23:44:14 GMT; domain=.yjv.com')
        );
        $this->assertEquals(
            new Cookie('name', 'value', strtotime('Tue, 31-Dec-2013 23:44:14 GMT'), '/path-to-url', '.yjv.com', false, false), 
            Factory::createFromSetCookieHeader('name=value; expires=Tue, 31-Dec-2013 23:44:14 GMT; domain=.yjv.com; path=/path-to-url')
        );
        $this->assertEquals(
            new Cookie('name', 'value', strtotime('Tue, 31-Dec-2013 23:44:14 GMT'), '/path-to-url', '.yjv.com', true, false), 
            Factory::createFromSetCookieHeader('name=value; expires=Tue, 31-Dec-2013 23:44:14 GMT; domain=.yjv.com; path=/path-to-url; secure')
        );
        $this->assertEquals(
            new Cookie('name', 'value', strtotime('Tue, 31-Dec-2013 23:44:14 GMT'), '/path-to-url', '.yjv.com', true, true), 
            Factory::createFromSetCookieHeader('name=value; expires=Tue, 31-Dec-2013 23:44:14 GMT; domain=.yjv.com; path=/path-to-url; httponly; secure')
        );
        $this->assertEquals(
            new Cookie('name', 'value', strtotime('Tue, 31-Dec-2013 23:44:14 GMT'), '/path-to-url', '.yjv.com', false, true), 
            Factory::createFromSetCookieHeader('name=value; expires=Tue, 31-Dec-2013 23:44:14 GMT; domain=.yjv.com; path=/path-to-url; httponly')
        );
    }
    
    public function testCreateMultipleFromCookieHeader()
    {
        $this->assertEquals(array(
            new Cookie('name', 'value'),
            new Cookie('name2', 'value=othervalue'),
            new Cookie('dsadsa', 'asddsa')
        ), Factory::createMultipleFromCookieHeader('name=value; name2=value=othervalue; dsadsa=asddsa;'));
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The header sent is malformed
     */
    public function testCreateFromSetCookieHeaderWithMalformedHeader()
    {
        Factory::createFromSetCookieHeader('sdffds&');
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The header sent is malformed
     */
    public function testCreateFromSetCookieHeaderWithMalformedHeader2()
    {
        Factory::createFromSetCookieHeader('sdffds&=');
    }
}
