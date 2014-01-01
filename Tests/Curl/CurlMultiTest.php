<?php
namespace Yjv\HttpQueue\Tests\Curl;

use Yjv\HttpQueue\Connection\FinishedHandleInformation;

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
    
    public function testAddHandle()
    {
        $testCase = $this;
        $resource = $this->resource;
        $singleResource = "123";
        $singleHandle = Mockery::mock('Yjv\HttpQueue\Curl\CurlHandle')
            ->shouldReceive('getResource')
            ->twice()
            ->andReturn($singleResource)
            ->getMock()
        ;
        
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_add_handle', function(
            $passedResource,
            $passedSingleResource
        ) use (
            $testCase, 
            $resource, 
            $singleResource
        ) {
            $testCase->assertSame($resource, $passedResource);
            $testCase->assertSame($singleResource, $passedSingleResource);
        });
        $this->assertSame($this->handle, $this->handle->addHandle($singleHandle));
    }
    
    public function testRemoveHandle()
    {
        $testCase = $this;
        $resource = $this->resource;
        $singleResource = "123";
        $singleHandle = Mockery::mock('Yjv\HttpQueue\Curl\CurlHandle')
            ->shouldReceive('getResource')
            ->twice()
            ->andReturn($singleResource)
            ->getMock()
        ;
        
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_remove_handle', function(
            $passedResource,
            $passedSingleResource
        ) use (
            $testCase, 
            $resource, 
            $singleResource
        ) {
            $testCase->assertSame($resource, $passedResource);
            $testCase->assertSame($singleResource, $passedSingleResource);
        });
        $this->assertSame($this->handle, $this->handle->removeHandle($singleHandle));
    }
    
    public function testExecute()
    {
        $testCase = $this;
        $resource = $this->resource;
        $callCount = 0;
        
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_exec', function(
            $passedResource
        ) use (
            $testCase, 
            $resource,
            &$callCount
        ) {
            $callCount++;
            
            $testCase->assertSame($resource, $passedResource);
            
            if ($callCount > 3) {
                
                return CurlMulti::STATUS_OK;
            }
            
            return CurlMulti::STATUS_PERFORMING;
        });
        $this->handle->execute();
        $this->assertEquals(4, $callCount);
    }
    
    /**
     * @expectedException Yjv\HttpQueue\Curl\CurlMultiException
     * @expectedExceptionMessage cURL error: 1 (CURLM_BAD_HANDLE): cURL message: The passed-in handle is not a valid curl multi handle.
     * @return string
     */
    public function testExecuteWhereReturnUnexpected()
    {
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_exec', function()
        {
            return CURLM_BAD_HANDLE;
        });
        $this->handle->execute();
    }
    
    public function testGetHandlResponseContent()
    {
        $testCase = $this;
        $singleResource = new \stdClass();
        $singleHandle = Mockery::mock('Yjv\HttpQueue\Curl\CurlHandle')
            ->shouldReceive('getResource')
            ->once()
            ->andReturn($singleResource)
            ->getMock()
        ;
        $responseContent = 'xzcczxfsdfsdfsd';
        
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_getcontent', function(
            $passedResource
        ) use (
            $testCase, 
            $singleResource,
            $responseContent
        ) {
            $testCase->assertSame($singleResource, $passedResource);
            return $responseContent;
        });
        $this->assertEquals($responseContent, $this->handle->getHandleResponseContent($singleHandle));
    }
    
    public function testGetStillExecutingCount()
    {
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_exec', function($resource, &$stillExecutingCount)
        {
            $stillExecutingCount = 4;
        });
        $this->assertEquals(4, $this->handle->getStillRunningCount());
    }
    
    public function testGetFinishedHandles()
    {
        $infos = array(
            array('handle' => '123', 'result' => 1, 'msg' => 'msg1'),
            array('handle' => '456', 'result' => 2, 'msg' => 'msg2'),
            array('handle' => '789', 'result' => 3, 'msg' => 'msg3'),
            false
        );
        $handle1 = Mockery::mock('Yjv\HttpQueue\Curl\CurlHandle')
            ->shouldReceive('getResource')
            ->twice()
            ->andReturn('123')
            ->getMock()
        ;
        $handle2 = Mockery::mock('Yjv\HttpQueue\Curl\CurlHandle')
            ->shouldReceive('getResource')
            ->twice()
            ->andReturn('456')
            ->getMock()
        ;
        $handle3 = Mockery::mock('Yjv\HttpQueue\Curl\CurlHandle')
            ->shouldReceive('getResource')
            ->twice()
            ->andReturn('789')
            ->getMock()
        ;
        $finishedHandles = array(
            new FinishedHandleInformation($handle1, $infos[0]['result'], $infos[0]['msg']),
            new FinishedHandleInformation($handle2, $infos[1]['result'], $infos[1]['msg']),
            new FinishedHandleInformation($handle3, $infos[2]['result'], $infos[2]['msg']),
        );
        $resource = $this->resource;
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_add_handle', function(){});
        $this->handle->addHandle($handle1)->addHandle($handle2)->addHandle($handle3);
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_info_read', function($passedResource) use ($resource, $infos){
            
            static $count = 0;
            return $infos[$count++];
        });
        
        $this->assertEquals($finishedHandles, $this->handle->getFinishedHandles());
    }
    
    public function testSelectWhereSelectReturnsANumberNotNegativeOne()
    {
        $testCase = $this;
        $resource = $this->resource;
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_select', function(
            $passedResource,
            $passedTimeout
        ) use (
            $testCase, 
            $resource
        ) {
            $testCase->assertEquals(0.001, $passedTimeout);
            $testCase->assertSame($resource, $passedResource);
            return 12;
        });
        $this->assertEquals(12, $this->handle->select(1.2));
    }
    
    public function testSelectWhereSelectReturnsANegativeOne()
    {
        $testCase = $this;
        $resource = $this->resource;
        $selectCount = 0;
        $execCount = 0;
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_select', function(
            $passedResource,
            $passedTimeout
        ) use (
            $testCase, 
            $resource,
            &$selectCount
        ) {
            $selectCount++;
            $testCase->assertSame($resource, $passedResource);
            
            if ($selectCount == 1) {

                $testCase->assertEquals(0.001, $passedTimeout);
            } elseif($selectCount == 2) {
                
                $testCase->assertEquals(1.2 - 0.001, $passedTimeout);
            } else {
                
                $testCase->assertEquals(1.2, $passedTimeout);
            }
            
            return -1;
        });
        InternalFunctionMocker::mockFunction($this->handle, 'usleep', function(
            $time
        ) use (
            $testCase,
            &$selectCount
        ) {
            $testCase->assertEquals(1, $selectCount);
            $testCase->assertEquals(150, $time);
        });
        InternalFunctionMocker::mockFunction($this->handle, 'curl_multi_exec', function(
            $passedResource,
            &$passedTimeout
        ) use (
            $testCase, 
            $resource,
            &$execCount,
            &$selectCount
        ) {
            $execCount++;
            $testCase->assertEquals(1, $selectCount);
            $testCase->assertSame($resource, $passedResource);
        });
        $this->assertEquals(0, $this->handle->select(1.2));
        $this->assertEquals(0, $this->handle->select(1.2));
    }
    
    public function tearDown()
    {
        InternalFunctionMocker::clearMockedFunctions();
    }
}
