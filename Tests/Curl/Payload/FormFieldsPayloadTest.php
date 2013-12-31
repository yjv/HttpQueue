<?php
namespace Yjv\HttpQueue\Tests\Curl\Payload;

use Yjv\HttpQueue\Curl\Payload\FormFieldsPayload;
use Mockery;

class FormFieldsPayloadTest extends \PHPUnit_Framework_TestCase
{
    protected $payload;
    
    public function setUp()
    {
        $this->payload = new FormFieldsPayload();
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage $handle must be an instance of Yjv\HttpQueue\Curl\CurlHandle
     */
    public function testSetHandleWithNonCurlHandle()
    {
        $handle = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $this->payload->setHandle($handle);
    }
    
    public function testSetHandleWithCurlHandle()
    {
        $handle = Mockery::mock('Yjv\HttpQueue\Curl\CurlHandle')
            ->shouldReceive('setOption')
            ->once()
            ->with(CURLOPT_POST, true)
            ->getMock()
        ;
        $this->assertSame($this->payload, $this->payload->setHandle($handle));
    }
    
    public function testGetPayloadData()
    {
        $this->payload['key'] = array(
            'subkey1' => Mockery::mock('Yjv\HttpQueue\Curl\CurlFileInterface')
                ->shouldReceive('getCurlValue')
                ->once()
                ->andReturn('curl_file')
                ->getMock()
            ,
            'subkey2' => 'value'
        );
        $this->assertEquals(array(
            'key[subkey1]' => 'curl_file',
            'key[subkey2]' => 'value'
        ), $this->payload->getPayloadData());
    }

    public function testContentGetters()
    {
        $this->assertNull($this->payload->getContentType());
        $this->assertNull($this->payload->getContentLength());
    }
}