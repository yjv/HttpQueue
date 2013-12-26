<?php
namespace Yjv\HttpQueue\Tests;

use Yjv\HttpQueue\AbstractHeaderBag;

use Symfony\Component\HttpFoundation\Cookie;

use Symfony\Component\HttpFoundation\Tests\HeaderBagTest;

abstract class AbstractHeaderBadTest extends HeaderBagTest
{
    /**
     * @covers Symfony\Component\HttpFoundation\ResponseHeaderBag::allPreserveCase
     * @dataProvider provideAllPreserveCase
     */
    public function testAllPreserveCase($headers, $expected)
    {
        $bag = $this->createHeaderBag($headers);
    
        $this->assertEquals($expected, $bag->allPreserveCase(), '->allPreserveCase() gets all input keys in original case');
    }

    public function provideAllPreserveCase()
    {
        return array(
                array(
                        array('fOo' => 'BAR'),
                        array('fOo' => array('BAR'))
                ),
                array(
                        array('ETag' => 'xyzzy'),
                        array('ETag' => array('xyzzy'))
                ),
                array(
                        array('Content-MD5' => 'Q2hlY2sgSW50ZWdyaXR5IQ=='),
                        array('Content-MD5' => array('Q2hlY2sgSW50ZWdyaXR5IQ=='))
                ),
                array(
                        array('P3P' => 'CP="CAO PSA OUR"'),
                        array('P3P' => array('CP="CAO PSA OUR"'))
                ),
                array(
                        array('WWW-Authenticate' => 'Basic realm="WallyWorld"'),
                        array('WWW-Authenticate' => array('Basic realm="WallyWorld"'))
                ),
                array(
                        array('X-UA-Compatible' => 'IE=edge,chrome=1'),
                        array('X-UA-Compatible' => array('IE=edge,chrome=1'))
                ),
                array(
                        array('X-XSS-Protection' => '1; mode=block'),
                        array('X-XSS-Protection' => array('1; mode=block'))
                ),
        );
    }
    
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetCookiesWithInvalidArgument()
    {
        $bag = $this->createHeaderBag();
    
        $cookies = $bag->getCookies('invalid_argument');
    }
    
    public function testToStringDoesntMessUpHeaders()
    {
        $headers = $this->createHeaderBag();
    
        $headers->set('Location', 'http://www.symfony.com');
        $headers->set('Content-type', 'text/html');
    
        (string) $headers;
    
        $allHeaders = $headers->allPreserveCase();
        $this->assertEquals(array('http://www.symfony.com'), $allHeaders['Location']);
        $this->assertEquals(array('text/html'), $allHeaders['Content-type']);
    }
    
    abstract protected function createHeaderBag(array $headers = array());
}
