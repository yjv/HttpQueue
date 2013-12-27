<?php
namespace Yjv\HttpQueue\Tests;

use Yjv\HttpQueue\RequestResponseHandleMap;
use Mockery;

class RequestResponseHandleMapTest extends \PHPUnit_Framework_TestCase
{
    protected $handleMap;
    
    public function setUp()
    {
        $this->handleMap = new RequestResponseHandleMap();
    }
    
    public function testGettersSetters()
    {
        $handle1 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $handle2 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $handle3 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $handle4 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $request1 = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $request2 = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $request3 = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $request4 = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $response1 = Mockery::mock('Yjv\HttpQueue\Response\ResponseInterface');
        $response2 = Mockery::mock('Yjv\HttpQueue\Response\ResponseInterface');
        $response3 = Mockery::mock('Yjv\HttpQueue\Response\ResponseInterface');
        $response4 = Mockery::mock('Yjv\HttpQueue\Response\ResponseInterface');
        
        $this->assertSame($this->handleMap, $this->handleMap->setRequest($handle1, $request1));
        $this->assertNull($this->handleMap->getRequest($handle2));
        $this->assertNull($this->handleMap->getRequest($handle3));
        $this->assertSame($this->handleMap, $this->handleMap->setRequest($handle3, $request3));
        $this->assertSame($this->handleMap, $this->handleMap->setRequest($handle4, $request4));
        $this->assertEquals(array(
            $request1,
            $request3,
            $request4
        ), $this->handleMap->getRequests());
        $this->assertSame($this->handleMap, $this->handleMap->setResponse($handle1, $response1));
        $this->assertNull($this->handleMap->getResponse($handle2));
        $this->assertNull($this->handleMap->getResponse($handle3));
        $this->assertSame($this->handleMap, $this->handleMap->setResponse($handle3, $response3));
        $this->assertSame($this->handleMap, $this->handleMap->setResponse($handle4, $response4));
        $this->assertEquals(array(
            $response1,
            $response3,
            $response4
        ), $this->handleMap->getResponses());
        $this->assertSame($this->handleMap, $this->handleMap->clear($handle1));
        $this->assertEquals(array(
            $request3,
            $request4
        ), $this->handleMap->getRequests());
        $this->assertEquals(array(
            $response3,
            $response4
        ), $this->handleMap->getResponses());
        $this->assertSame($this->handleMap, $this->handleMap->clear());
        $this->assertEquals(array(), $this->handleMap->getRequests());
        $this->assertEquals(array(), $this->handleMap->getResponses());
    }
}
