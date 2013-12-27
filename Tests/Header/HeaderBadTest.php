<?php
namespace Yjv\HttpQueue\Tests\Header;

use Yjv\HttpQueue\Header\HeaderBag;

use Symfony\Component\HttpFoundation\Tests\HeaderBagTest as BaseHeaderBagTest;

class HeaderBadTest extends BaseHeaderBagTest
{
    /**
     * @covers Yjv\HttpQueue\Header\HeaderBag::allPreserveCase
     * @dataProvider provideAllPreserveCase
     */
    public function testAllPreserveCase($headers, $expected)
    {
        $bag = new HeaderBag($headers);
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
    
    public function testToStringDoesntMessUpHeaders()
    {
        $headers = new HeaderBag();
    
        $headers->set('Location', 'http://www.symfony.com');
        $headers->set('Content-type', 'text/html');
    
        (string) $headers;
    
        $allHeaders = $headers->allPreserveCase();
        $this->assertEquals(array('http://www.symfony.com'), $allHeaders['Location']);
        $this->assertEquals(array('text/html'), $allHeaders['Content-type']);
    }
    
    public function testReplaceClearsOriginalHeaders()
    {
        $headers = new HeaderBag();
    
        $headers->set('Location', 'http://www.symfony.com');
        $headers->set('COntent-type', 'text/html');
        
        $headers->replace(array('Key' => 'value', 'ASDdas' => 'vxcDAwe'));
        $this->assertCount(2, $headers);
        $allHeaders = $headers->allPreserveCase();
        $this->assertEquals(array('value'), $allHeaders['Key']);
        $this->assertEquals(array('vxcDAwe'), $allHeaders['ASDdas']);
    }
    
    public function testRemoveClearsHeader()
    {
        $headers = new HeaderBag();
    
        $headers->set('LocAtion', 'http://www.symfony.com');
        $headers->set('COntent-type', 'text/html');
        
        $headers->remove('COntent-type');
        $this->assertCount(1, $headers);
        $allHeaders = $headers->allPreserveCase();
        $this->assertEquals(array('http://www.symfony.com'), $allHeaders['LocAtion']);
    }
    
    public function testAllPreserveCaseFlattened()
    {
        $headers = new HeaderBag(array(
            'Key' => 'value', 
            'Key2' => array('value2', 'value3'),
            'Key3' => 'value4'
        ));
        $this->assertEquals(array(
            'Key: value',
            'Key2: value2',
            'Key2: value3',
            'Key3: value4',
        ), $headers->allPreserveCaseFlattened());
    }
    
    public function testAllPreserveCaseFlattenedWithFiltering()
    {
        $headers = new HeaderBag(array(
            'Key' => 'value', 
            'Key2' => array('value2', 'value3'),
            'Key3' => 'value4',
            'Key4' => array('value5', 'value6')
        ));
        $this->assertEquals(array(
            'Key: value',
            'Key4: value5',
            'Key4: value6',
        ), $headers->allPreserveCaseFlattened(array('Key2', 'Key3')));
    }
}
