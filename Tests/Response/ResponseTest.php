<?php
namespace Yjv\HttpQueue\Tests\Request;

use Yjv\HttpQueue\Header\HeaderBag;
use Yjv\HttpQueue\Response\Response;
use Mockery;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    protected $response;
    
    public function setUp()
    {
        $this->response = new Response(200);
    }
    
    public function testGettersSetters()
    {
        $this->assertEquals(200, $this->response->getCode());
        $this->assertEquals(Response::$statusTexts[200], $this->response->getStatusMessage());
        $this->assertSame($this->response, $this->response->setCode(302));
        $this->assertEquals(302, $this->response->getCode());
        $this->assertEquals(Response::$statusTexts[302], $this->response->getStatusMessage());
        $this->assertSame($this->response, $this->response->setStatusMessage('status message'));
        $this->assertEquals('status message', $this->response->getStatusMessage());
        $this->assertInstanceOf('Yjv\HttpQueue\Header\HeaderBag', $this->response->getHeaders());
        $this->assertSame($this->response, $this->response->setHeaders(array('key' => 'value', 'key2' => 'value2')));
        $this->assertEquals(
                new HeaderBag(array('key' => 'value', 'key2' => 'value2')),
                $this->response->getHeaders()
        );
        $headers = new HeaderBag();
        $this->assertSame($this->response, $this->response->setHeaders($headers));
        $this->assertSame($headers, $this->response->getHeaders());
        $this->assertNull($this->response->getBody());
        $payload = Mockery::mock('Yjv\HttpQueue\Transport\Payload\PayloadDestinationInterface');
        $this->assertSame($this->response, $this->response->setBody($payload));
        $this->assertSame($payload, $this->response->getBody());
    }
    
    public function testCastToString()
    {
        $headers = Mockery::mock('Yjv\HttpQueue\Header\HeaderBag')
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('headers')
            ->getMock()
        ;
        $body = Mockery::mock('Yjv\HttpQueue\Transport\Payload\PayloadDestinationInterface')
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('body')
            ->getMock()
        ;
        $this->response
            ->setHeaders($headers)
            ->setBody($body)
        ;
        $this->assertEquals("headers\r\nbody", (string)$this->response);
        
        
    }
}