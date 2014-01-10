<?php
namespace Yjv\HttpQueue\Tests\Curl;

use Yjv\HttpQueue\Curl\CurlEvents;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Curl\CurlHandle;

use Mockery;

class CurlHandleTest extends \PHPUnit_Framework_TestCase
{
    protected $resource;
    protected $handle;
    
    public function setUp()
    {
        $resource = $this->resource = new \stdClass();
        InternalFunctionMocker::mockFunction('Yjv\HttpQueue\Curl\CurlHandle', 'curl_init', function() use ($resource)
        {
            return $resource;
        });
        $this->handle = new CurlHandle();
    }
    
    public function testGetResource()
    {
        $this->assertSame($this->resource, $this->handle->getResource());
    }
    
    public function testOptionGettersSetters()
    {
        define('NAME_OPTION', 354);
        $this->assertEquals(array(), $this->handle->getOptions());
        $this->assertNull($this->handle->getOption('name'));
        $this->assertEquals('default', $this->handle->getOption('name', 'default'));
        $testCase = $this;
        $resource = $this->resource;
        InternalFunctionMocker::mockFunction($this->handle, 'curl_setopt', function(
            $passedResource, 
            $name, 
            $value
        ) use ($testCase, $resource)
        {
            $testCase->assertSame($resource, $passedResource);
            $testCase->assertEquals('name', $name);
            $testCase->assertEquals('value', $value);
        });
        $this->assertSame($this->handle, $this->handle->setOption('name', 'value'));
        $this->assertEquals(array('name' => 'value'), $this->handle->getOptions());
        $this->assertEquals('value', $this->handle->getOption('name'));
        $this->assertEquals('value', $this->handle->getOption('name', 'default'));
        $options = array(NAME_OPTION => 'value2', 'name3' => 'value3');
        InternalFunctionMocker::mockFunction($this->handle, 'curl_setopt_array', function(
            $passedResource,
            $passedOptions
        ) use ($testCase, $resource, $options)
        {
            $testCase->assertSame($resource, $passedResource);
            $testCase->assertEquals($options, $passedOptions);
        });
        $this->assertSame($this->handle, $this->handle->setOptions($options));
        $this->assertEquals('value', $this->handle->getOption('name'));
        $this->assertEquals('value2', $this->handle->getOption(NAME_OPTION));
        $this->assertEquals(array(
            'name' => 'value',
            NAME_OPTION => 'value2', 
            'name3' => 'value3'
        ), $this->handle->getOptions());
        $options = array('name' => 'value4');
        InternalFunctionMocker::mockFunction($this->handle, 'curl_setopt_array', function(
            $passedResource,
            $passedOptions
        ) use ($testCase, $resource, $options)
        {
            $testCase->assertSame($resource, $passedResource);
            $testCase->assertEquals($options, $passedOptions);
        });
        $this->assertSame($this->handle, $this->handle->setOptions($options));
        $this->assertEquals(array(
            'name' => 'value4',
            NAME_OPTION => 'value2', 
            'name3' => 'value3'
        ), $this->handle->getOptions());
    }
    
    public function testClose()
    {
        $testCase = $this;
        $resource = $this->resource;
        InternalFunctionMocker::mockFunction($this->handle, 'curl_close', function(
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
        InternalFunctionMocker::mockFunction($this->handle, 'curl_close', function(
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
        InternalFunctionMocker::mockFunction($this->handle, 'curl_close', function(
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
    
    public function testExecute()
    {
        InternalFunctionMocker::mockFunction($this->handle, 'curl_exec', function(){return 'result';});
        $this->assertEquals('result', $this->handle->execute());
    }
    
    public function test__clone()
    {
        $newResource = new \stdClass();
        $testCase = $this;
        $resource = $this->resource;
        InternalFunctionMocker::mockFunction($this->handle, 'curl_copy_handle', function(
            $passedResource
        ) use ($testCase, $resource, $newResource)
        {
            $testCase->assertSame($resource, $passedResource);
            return $newResource;
        });
        $newHandle = clone $this->handle;
        $this->assertSame($this->resource, $this->handle->getResource());
        $this->assertSame($newResource, $newHandle->getResource());
    }
    
    public function testGetLastTransferInfo()
    {
        $info = array('key' => 'value');
        $testCase = $this;
        $resource = $this->resource;
        InternalFunctionMocker::mockFunction($this->handle, 'curl_getinfo', function(
            $passedResource
        ) use ($testCase, $resource, $info)
        {
            $testCase->assertSame($resource, $passedResource);
            $testCase->assertCount(1, func_get_args());
            return $info;
        });
        $this->assertEquals($info, $this->handle->getLastTransferInfo());
        $option = 'option';
        $key = 'key312';
        InternalFunctionMocker::mockFunction($this->handle, 'curl_getinfo', function(
            $passedResource,
            $passedKey
        ) use ($testCase, $resource, $key, $option)
        {
            $testCase->assertSame($resource, $passedResource);
            $testCase->assertEquals($key, $passedKey);
            return $option;
        });
        $this->assertEquals($option, $this->handle->getLastTransferInfo($key));
    }
    
    public function testCallbackOptionForwarding()
    {
        $testCase = $this;
        $optionName = null;
        $additionalArgs = array();
        $options = array();
        $handle = $this->handle;
        $resource = $this->resource;
        
        foreach (array_keys(CurlEvents::getCallbackEvents()) as $callbackOption) {

            $options[$callbackOption] = function() use(
                &$optionName,
                &$additionalArgs,
                $callbackOption, 
                $testCase, 
                $handle
            ) {
                $args = func_get_args();
                $testCase->assertSame($handle, array_shift($args));
                $testCase->assertEquals($additionalArgs, $args);
                $testCase->assertEquals($callbackOption, $optionName);
            };
        };
        
        InternalFunctionMocker::mockFunction($this->handle, 'curl_setopt_array', function(
            $passedResource,
            array $options
        ) use ($testCase, $resource, &$optionName, &$additionalArgs)
        {
            foreach ($options as $name => $callback) {
                
                $args = array(rand(0, 1e6), rand(0, 1e6), rand(0, 1e6));
                $optionName = $name;
                $additionalArgs = $args;
                array_unshift($args, $resource);
                call_user_func_array($callback, $args);
            }
        });
        
        $this->handle->setOptions($options);
        
        $observer = Mockery::mock('Yjv\HttpQueue\Connection\HandleObserverInterface');
        $this->assertSame($this->handle, $this->handle->setObserver($observer));
        $this->assertSame($observer, $this->handle->getObserver());
        
        
        InternalFunctionMocker::mockFunction($this->handle, 'curl_setopt_array', function(
            $passedResource,
            array $options
        ) use ($testCase, $resource, $observer, $handle, &$optionName, &$additionalArgs)
        {
            foreach ($options as $name => $callback) {
                
                $args = array(rand(0, 1e6), rand(0, 1e6), rand(0, 1e6));
                $optionName = $name;
                $additionalArgs = $args;
                array_unshift($args, $resource);
                $observer
                    ->shouldReceive('handleEvent')
                    ->once()
                    ->with(
                        CurlEvents::getCallbackEvents($name),
                        $handle,
                        $additionalArgs
                    )
                ;
                call_user_func_array($callback, $args);
            }
        });
        
        $this->handle->setOptions($options);
    }
    
    public function testSetSourcePayloadWithNonStream()
    {
        $payload = Mockery::mock('Yjv\HttpQueue\Connection\Payload\SourcePayloadInterface')
            ->shouldReceive('setDestinationHandle')
            ->once()
            ->with($this->handle)
            ->getMock()
        ;
        $this->assertSame($this->handle, $this->handle->setSourcePayload($payload));
    }
    
    public function testSetSourcePayloadWithStream()
    {
        $data = 'stream_data';
        $amountToRead = rand(1, 1e6);
        $testCase = $this;
        $handle = $this->handle;
        $payload = Mockery::mock('Yjv\HttpQueue\Connection\Payload\SourceStreamInterface')
            ->shouldReceive('readStream')
            ->once()
            ->with($amountToRead)
            ->andReturn($data)
            ->getMock()
            ->shouldReceive('setDestinationHandle')
            ->once()
            ->with($this->handle)
            ->getMock()
        ;
        InternalFunctionMocker::mockFunction($this->handle, 'curl_setopt_array', function(
            $resource, 
            $options
        ) use ($testCase, $data, $amountToRead, $handle)
        {
            $testCase->assertCount(2, $options);
            $testCase->assertTrue($options[CURLOPT_UPLOAD]);
            $testCase->assertEquals($data, $options[CURLOPT_READFUNCTION]($handle, 2, $amountToRead));
        });
        $this->assertSame($this->handle, $this->handle->setSourcePayload($payload));
    }
    
    public function testSetDestinationPayloadWithNonStream()
    {
        $payload = Mockery::mock('Yjv\HttpQueue\Connection\Payload\DestinationPayloadInterface')
            ->shouldReceive('setSourceHandle')
            ->once()
            ->with($this->handle)
            ->getMock()
        ;
        $this->assertSame($this->handle, $this->handle->setDestinationPayload($payload));
    }
    
    public function testSetDestinationPayloadWithStream()
    {
        $data = 'stream_data';
        $testCase = $this;
        $handle = $this->handle;
        $payload = Mockery::mock('Yjv\HttpQueue\Connection\Payload\DestinationStreamInterface')
            ->shouldReceive('writeStream')
            ->once()
            ->with($data)
            ->andReturn(strlen($data))
            ->getMock()
            ->shouldReceive('setSourceHandle')
            ->once()
            ->with($this->handle)
            ->getMock()
        ;
        InternalFunctionMocker::mockFunction($this->handle, 'curl_setopt', function(
            $resource, 
            $name,
            $value
        ) use ($testCase, $data, $handle)
        {
            $testCase->assertEquals(CURLOPT_WRITEFUNCTION, $name);
            $testCase->assertEquals(strlen($data), $value($handle, $data));
        });
        $this->assertSame($this->handle, $this->handle->setDestinationPayload($payload));
    }
    
    public function tearDown()
    {
        InternalFunctionMocker::clearMockedFunctions();
    }
}
