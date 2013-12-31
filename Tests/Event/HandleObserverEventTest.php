<?php
namespace Yjv\HttpQueue\Tests\Event;

use Yjv\HttpQueue\Event\HandleObserverEvent;

use Yjv\HttpQueue\Event\HandleEvent;
use Mockery;

class HandleObserverEventTest extends HandleEventTest
{
    protected $handle;
    protected $args;
    
    public function setUp()
    {
        parent::setUp();
        $this->args = array('key' => 'value');
        $this->event = new HandleObserverEvent($this->queue, $this->request, $this->handle, $this->args);
    }
    
    public function testGettersSetters()
    {
        parent::testGettersSetters();
        $args = array('key2' => 'value2');
        $this->assertEquals($this->args, $this->event->getArgs());
        $this->assertSame($this->event, $this->event->setArgs($args));
        $this->assertEquals($args, $this->event->getArgs());
    }
}
