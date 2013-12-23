<?php
namespace Yjv\HttpQueue\Tests\Uri;

use Yjv\HttpQueue\Uri\Path;

class PathTest extends \PHPUnit_Framework_TestCase
{
    protected $path;
    
    public function setUp()
    {
        $this->path = new Path();
    }
    
    public function testGettersSetters()
    {
        $extension = 'ext';
        $this->assertEquals('', $this->path->getExtension());
        $this->assertSame($this->path, $this->path->setExtension($extension));
        $this->assertEquals($extension, $this->path->getExtension());
    }
    
    public function testStringConversion()
    {
        $pathString = '/asd%26%2Fasd/adsdasdsa.ertrte';
        $this->assertEquals(new Path(array('asd&/asd', 'adsdasdsa'), 'ertrte'), Path::createFromString($pathString));
        $this->assertEquals($pathString, (string)Path::createFromString($pathString));
        $this->assertEquals($pathString, (string)Path::createFromString('asd%26%2Fasd/adsdasdsa.ertrte'));
    }
}
