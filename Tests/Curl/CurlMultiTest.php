<?php
namespace Yjv\HttpQueue\Tests\Curl;

use Yjv\HttpQueue\Curl\CurlMulti;

use Yjv\HttpQueue\Curl\CurlEvents;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Curl\CurlHandle;

use Mockery;

class CurlMultiTest extends \PHPUnit_Framework_TestCase
{
    protected $resource;
    protected $handle;
    
    public function setUp()
    {
        $resource = $this->resource = new \stdClass();
        InternalFunctionMocker::mockFunction('Yjv\HttpQueue\Curl\CurlHandle', 'curl_multi_init', function() use ($resource)
        {
            return $resource;
        });
        $this->handle = new CurlMulti();
    }
    
    public function testGetResource()
    {
        $this->assertSame($this->resource, $this->handle->getResource());
    }
    
    public function testClose()
    {
        $testCase = $this;
        $resource = $this->resource;
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_close', function(
            $passedResource
        ) use ($testCase, $resource)
        {
            $testCase->assertSame($resource, $passedResource);
        });
        $this->handle->close();
    }
    
    public function testDestructWhereHandleNotClosed()
    {
        $testCase = $this;
        $resource = $this->resource;
        InternalFunctionMocker::mockFunction($this->handle, 'is_resource', function(){return true;});
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_close', function(
            $passedResource
        ) use ($testCase, $resource)
        {
            $testCase->assertSame($resource, $passedResource);
        });
        unset($this->handle);
    }
    
    public function testDestructWhereHandleClosed()
    {
        $testCase = $this;
        $resource = $this->resource;
        InternalFunctionMocker::mockFunction($this->handle, 'is_resource', function(){return false;});
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_close', function(
            $passedResource
        ) use ($testCase, $resource)
        {
            static $callCount = 0;
            $testCase->assertSame($resource, $passedResource);
            
            $callCount++;
            
            if ($callCount > 1) {
                
                $testCase->fail('tried to close an already closed handle');
            }
        });
        $this->handle->close();
        unset($this->handle);
    }
    
    public function test__clone()
    {
        $newResource = new \stdClass();
        $testCase = $this;
        $resource = $this->resource;
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_init', function() use (
            $testCase, 
            $resource, 
            $newResource
        ) {
            return $newResource;
        });
        $newHandle = clone $this->handle;
        $this->assertSame($this->resource, $this->handle->getResource());
        $this->assertSame($newResource, $newHandle->getResource());
    }
    
    public function tearDown()
    {
        InternalFunctionMocker::clearMockedFunctions();
    }
}
