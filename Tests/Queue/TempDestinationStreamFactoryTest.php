<?php
namespace Yjv\HttpQueue\Tests\Queue;

use Yjv\HttpQueue\Connection\Payload\StreamPayload;
use Mockery;
use Yjv\HttpQueue\Queue\TempDestinationStreamFactory;

class TempDestinationStreamFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;
    
    public function setUp()
    {
        $this->factory = new TempDestinationStreamFactory();
    }
    
    public function testGetDestinationPayload()
    {
        $payload = $this->factory->getDestinationPayload(
            Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface'), 
            Mockery::mock('Yjv\HttpQueue\Request\RequestInterface'), 
            Mockery::mock('Yjv\HttpQueue\Response\ResponseInterface')
        );
        $this->assertInstanceOf('Yjv\HttpQueue\Connection\Payload\StreamPayload', $payload);
        $this->assertEquals('TEMP', $payload->getStreamType());
    }
}
