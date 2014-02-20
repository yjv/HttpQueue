<?php
namespace Yjv\HttpQueue\Tests\Queue;

use Yjv\HttpQueue\Transport\Payload\StreamPayloadHolder;
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
            Mockery::mock('Yjv\HttpQueue\Transport\HandleInterface'),
            Mockery::mock('Yjv\HttpQueue\Request\RequestInterface'), 
            Mockery::mock('Yjv\HttpQueue\Response\ResponseInterface')
        );
        $this->assertInstanceOf('Yjv\HttpQueue\Transport\Payload\StreamPayloadHolder', $payload);
        $this->assertEquals('TEMP', $payload->getStreamType());
    }
}
