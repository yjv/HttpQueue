<?php
namespace Yjv\HttpQueue\Tests\Request;

use Yjv\HttpQueue\Header\HeaderBag;
use Yjv\HttpQueue\Request\RequestInterface;
use Yjv\HttpQueue\Request\RequestHeaderBag;
use Yjv\HttpQueue\Uri\Factory as UrlFactory;
use Yjv\HttpQueue\Request\Request;
use Mockery;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    protected $request;
    
    public function setUp()
    {
        $this->request = new Request('');
    }
    
    public function testGettersSetters()
    {
        $this->assertInstanceOf('Yjv\HttpQueue\Uri\Uri', $this->request->getUrl());
        $this->assertInstanceOf('Yjv\HttpQueue\Header\HeaderBag', $this->request->getHeaders());
        $this->assertEquals(RequestInterface::METHOD_GET, $this->request->getMethod());
        $this->assertSame($this->request, $this->request->setMethod(RequestInterface::METHOD_POST));
        $this->assertEquals(RequestInterface::METHOD_POST, $this->request->getMethod());
        $this->assertNull($this->request->getBody());
        $uriString = 'http://www.google.com/page';
        $this->assertSame($this->request, $this->request->setUrl($uriString));
        $this->assertEquals(
            UrlFactory::createUriFromString($uriString), 
            $this->request->getUrl()
        );
        $this->assertSame($this->request, $this->request->setHeaders(array('key' => 'value', 'key2' => 'value2')));
        $this->assertEquals(
            new HeaderBag(array('key' => 'value', 'key2' => 'value2')), 
            $this->request->getHeaders()
        );
        $headers = new HeaderBag();
        $this->assertSame($this->request, $this->request->setHeaders($headers));
        $this->assertSame($headers, $this->request->getHeaders());
        $this->assertEquals(array(), $this->request->getOptions());
        $this->assertEquals('default', $this->request->getOption('name', 'default'));
        $this->assertSame($this->request, $this->request->setOption('name', 'value'));
        $this->assertEquals('value', $this->request->getOption('name'));
        $this->assertEquals(array('name' => 'value'), $this->request->getOptions());
        $this->assertEquals(array(), $this->request->getHandleOptions());
        $this->assertEquals('handle_default', $this->request->getHandleOption('handle_name', 'handle_default'));
        $this->assertSame($this->request, $this->request->setHandleOption('handle_name', 'handle_value'));
        $this->assertEquals('handle_value', $this->request->getHandleOption('handle_name'));
        $this->assertEquals(array('handle_name' => 'handle_value'), $this->request->getHandleOptions());
        $payload = Mockery::mock('Yjv\HttpQueue\Connection\Payload\SourcePayloadInterface');
        $this->assertSame($this->request, $this->request->setBody($payload));
        $this->assertSame($payload, $this->request->getBody());
    }
    
    public function testCastToString()
    {
        $headers = Mockery::mock('Yjv\HttpQueue\Header\HeaderBag')
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('headers')
            ->getMock()
        ;
        $body = Mockery::mock('Yjv\HttpQueue\Connection\Payload\SourcePayloadInterface')
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('body')
            ->getMock()
        ;
        $this->request
            ->setHeaders($headers)
            ->setBody($body)
        ;
        $this->assertEquals("headers\r\nbody", (string)$this->request);
    }
}
