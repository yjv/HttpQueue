<?php
namespace Yjv\HttpQueue\Tests\Curl;

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

use Yjv\HttpQueue\Curl\CurlFile;

class CurlFileTest extends \PHPUnit_Framework_TestCase
{
    protected $file;
    
    public function setUp()
    {
        $this->file = new CurlFile(__FILE__);
    }
    
    public function testGettersSetters()
    {
        $this->assertSame($this->file, $this->file->setMimeType('mimeType'));
        $this->assertEquals('mimeType', $this->file->getMimeType());
        $this->assertSame($this->file, $this->file->setUploadName('uploadName'));
        $this->assertEquals('uploadName', $this->file->getUploadName());
    }
    
    public function testMimeTypeDefaultsOnEmpty()
    {
        $guesser = MimeTypeGuesser::getInstance();
        $this->assertEquals($guesser->guess($this->file->getPathname()), $this->file->getMimeType());
    }
    
    public function testUploadNameDefaultsOnEmpty()
    {
        $this->assertEquals($this->file->getBasename('.'.$this->file->getExtension()), $this->file->getUploadName());
    }
    
    public function testGetCurlValue()
    {
        if (class_exists('CURLFile')) {
            
            $this->assertEquals(new \CURLFile(
                $this->file->getRealPath(), 
                $this->file->getMimeType(), 
                $this->file->getUploadName()
            ), $this->file->getCurlValue());
        } else {

            // Use the old style if using an older version of PHP
            $this->assertEquals(sprintf(
                '@%s;filename=%s;type=%s', 
                $this->file->getRealPath(), 
                $this->file->getUploadName(), 
                $this->file->getMimeType()
            ), $this->file->getCurlValue());
        }
    }
}
