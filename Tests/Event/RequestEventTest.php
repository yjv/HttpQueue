<?php
namespace Yjv\HttpQueue\Tests\Event;

use Yjv\HttpQueue\Event\RequestEvent;
use Mockery;

class RequestEventTest extends \PHPUnit_Framework_TestCase
{
    protected $event;
    protected $queue;
    protected $request;
    
    public function setUp()
    {
        $this->queue = Mockery::mock('Yjv\HttpQueue\Queue\QueueInterface');
        $this->request = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $this->event = new RequestEvent($this->queue, $this->request);
    }
    
    public function testGettersSetters()
    {
        $this->assertSame($this->queue, $this->event->getQueue());
        $this->assertSame($this->request, $this->event->getRequest());
        $request = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $this->assertSame($this->event, $this->event->setRequest($request));
        $this->assertSame($request, $this->event->getRequest());
    }
}
