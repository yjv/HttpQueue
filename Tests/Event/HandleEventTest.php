<?php
namespace Yjv\HttpQueue\Tests\Event;

use Yjv\HttpQueue\Event\HandleEvent;
use Mockery;

class HandleEventTest extends RequestEventTest
{
    protected $handle;
    
    public function setUp()
    {
        parent::setUp();
        $this->handle = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $this->event = new HandleEvent($this->queue, $this->request, $this->handle);
    }
    
    public function testGettersSetters()
    {
        parent::testGettersSetters();
        $handle = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $this->assertSame($this->handle, $this->event->getHandle());
        $this->assertSame($this->event, $this->event->setHandle($handle));
        $this->assertSame($handle, $this->event->getHandle());
    }
}
