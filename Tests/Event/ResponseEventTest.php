<?php
namespace Yjv\HttpQueue\Tests\Event;

use Yjv\HttpQueue\Event\ResponseEvent;

use Yjv\HttpQueue\Event\HandleEvent;
use Mockery;

class ResponseEventTest extends RequestEventTest
{
    protected $response;
    
    public function setUp()
    {
        parent::setUp();
        $this->response = Mockery::mock('Yjv\HttpQueue\Response\ResponseInterface');
        $this->event = new ResponseEvent($this->queue, $this->request, $this->response);
    }
    
    public function testGettersSetters()
    {
        parent::testGettersSetters();
        $response = Mockery::mock('Yjv\HttpQueue\Response\ResponseInterface');
        $this->assertSame($this->response, $this->event->getResponse());
        $this->assertSame($this->event, $this->event->setResponse($response));
        $this->assertSame($response, $this->event->getResponse());
    }
}
