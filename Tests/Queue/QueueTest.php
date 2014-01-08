<?php
namespace Yjv\HtpQueue\Tests\Queue;

use Yjv\HttpQueue\Event\HandleObserverEvent;

use Yjv\HttpQueue\Event\ResponseEvent;

use Yjv\HttpQueue\Event\RequestEvents;

use Yjv\HttpQueue\Event\HandleEvent;

use Yjv\HttpQueue\Queue\Queue;
use Mockery;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    protected $queue;
    protected $config;
    protected $dispatcher;
    protected $handleFactory;
    protected $responseFactory;
    protected $multiHandle;
    protected $handleMap;
    
    public function setUp()
    {
        $this->dispatcher = Mockery::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->handleFactory = Mockery::mock('Yjv\HttpQueue\Queue\HandleFactoryInterface');
        $this->responseFactory = Mockery::mock('Yjv\HttpQueue\Queue\ResponseFactoryInterface');
        $this->multiHandle = Mockery::mock('Yjv\HttpQueue\Connection\MultiHandleInterface');
        $this->handleMap = Mockery::mock('Yjv\HttpQueue\HandleMap\RequestResponseHandleMap');
        $this->config = Mockery::mock('Yjv\HttpQueue\Queue\QueueConfigInterface')
            ->shouldReceive('getEventDispatcher')
            ->andReturn($this->dispatcher)
            ->getMock()
            ->shouldReceive('getHandleFactory')
            ->andReturn($this->handleFactory)
            ->getMock()
            ->shouldReceive('getResponseFactory')
            ->andReturn($this->responseFactory)
            ->getMock()
            ->shouldReceive('getMultiHandle')
            ->andReturn($this->multiHandle)
            ->getMock()
            ->shouldReceive('getHandleMap')
            ->andReturn($this->handleMap)
            ->getMock()
        ;
        $this->queue = new Queue($this->config);
        
    }
    
    public function testQueueWherePreEventDoesNotReturnAHandle()
    {
        $testCase = $this;
        $request = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $handle1 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $handle2 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $expectedPreEvent = new HandleEvent($this->queue, $request);
        $expectedPostEvent = new HandleEvent($this->queue, $request, $handle1);
        
        $this->dispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(RequestEvents::PRE_CREATE_HANDLE, Mockery::on(function($event) use ($testCase, $expectedPreEvent){
                
                $testCase->assertEquals($expectedPreEvent, $event);
                return true;
            }))
            ->getMock()
            ->shouldReceive('dispatch')
            ->once()
            ->with(RequestEvents::POST_CREATE_HANDLE, Mockery::on(function($event) use ($testCase, $expectedPostEvent, $handle2){
                
                $testCase->assertEquals($expectedPostEvent, $event);
                $event->setHandle($handle2);
                return true;
            }))
            ->getMock()
        ;
        $this->handleFactory
            ->shouldReceive('createHandle')
            ->once()
            ->with($request)
            ->andReturn($handle1)
        ;
        $this->handleMap
            ->shouldReceive('setRequest')
            ->once()
            ->with($handle2, $request)
        ;
        $this->multiHandle
            ->shouldReceive('addHandle')
            ->once()
            ->with($handle2)
        ;
        $this->queue->queue($request);
    }
    
    public function testQueueWherePreEventDoesReturnAHandle()
    {
        $testCase = $this;
        $request = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $handle1 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $handle2 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $expectedPreEvent = new HandleEvent($this->queue, $request);
        $expectedPostEvent = new HandleEvent($this->queue, $request, $handle1);
        
        $this->dispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(RequestEvents::PRE_CREATE_HANDLE, Mockery::on(function($event) use ($testCase, $expectedPreEvent, $handle2){
                
                $testCase->assertEquals($expectedPreEvent, $event);
                $event->setHandle($handle2);
                return true;
            }))
            ->getMock()
        ;
        $this->handleMap
            ->shouldReceive('setRequest')
            ->once()
            ->with($handle2, $request)
        ;
        $this->multiHandle
            ->shouldReceive('addHandle')
            ->once()
            ->with($handle2)
        ;
        $this->queue->queue($request);
    }
    
    public function testUnqueue()
    {
        $testCase = $this;
        $request = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $handle1 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $handle2 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        
        $this->handleMap
            ->shouldReceive('getHandles')
            ->once()
            ->with($request)
            ->andReturn(array($handle1, $handle2))
            ->getMock()
            ->shouldReceive('clear')
            ->once()
            ->with($handle1)
            ->getMock()
            ->shouldReceive('clear')
            ->once()
            ->with($handle2)
            ->getMock()
        ;
        
        $this->multiHandle
            ->shouldReceive('removeHandle')
            ->once()
            ->with($handle1)
            ->getMock()
            ->shouldReceive('removeHandle')
            ->once()
            ->with($handle2)
            ->getMock()
        ;
        $this->queue->unqueue($request);
    }
    
    public function testSend()
    {
        $testCase = $this;
        $handle1 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface')
            ->shouldReceive('setObserver')
            ->once()
            ->with($this->queue)
            ->getMock()
        ;
        $request1 = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $response1 = Mockery::mock('Yjv\HttpQueue\Response\ResponseInterface');
        $event1 = new HandleEvent($this->queue, $request1, $handle1);
        $responseEvent1 = new ResponseEvent($this->queue, $request1, $response1);
        
        $handle2 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface')
            ->shouldReceive('setObserver')
            ->once()
            ->with($this->queue)
            ->getMock()
        ;
        $request2 = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $event2 = new HandleEvent($this->queue, $request2, $handle2);
        $responseEvent2 = new ResponseEvent($this->queue, $request2);
        
        $handle3 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface')
            ->shouldReceive('setObserver')
            ->once()
            ->with($this->queue)
            ->getMock()
        ;
        $request3 = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $response3 = Mockery::mock('Yjv\HttpQueue\Response\ResponseInterface');
        $event3 = new HandleEvent($this->queue, $request3, $handle3);
        $responseEvent3 = new ResponseEvent($this->queue, $request3, $response3);
        
        $this->handleMap
            ->shouldReceive('getHandles')
            ->once()
            ->andReturn(array($handle1, $handle2, $handle3))
            ->getMock()
            ->shouldReceive('getRequest')
            ->once()
            ->with($handle1)
            ->andReturn($request1)
            ->getMock()
            ->shouldReceive('getRequest')
            ->once()
            ->with($handle2)
            ->andReturn($request2)
            ->getMock()
            ->shouldReceive('getRequest')
            ->once()
            ->with($handle3)
            ->andReturn($request3)
            ->getMock()
        ;
        $this->dispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(RequestEvents::PRE_SEND, Mockery::on(function(HandleEvent $event) use ($testCase, $event1){
                
                try {
                    $testCase->assertSame($event1->getQueue(), $event->getQueue());
                    $testCase->assertSame($event1->getRequest(), $event->getRequest());
                    $testCase->assertSame($event1->getHandle(), $event->getHandle());
                    return true;
                } catch(\PHPUnit_Framework_AssertionFailedError $e) {
                    return false;
                }
            }))
            ->getMock()
            ->shouldReceive('dispatch')
            ->once()
            ->with(RequestEvents::PRE_SEND, Mockery::on(function(HandleEvent $event) use ($testCase, $event2){
                
                try {
                    $testCase->assertSame($event2->getQueue(), $event->getQueue());
                    $testCase->assertSame($event2->getRequest(), $event->getRequest());
                    $testCase->assertSame($event2->getHandle(), $event->getHandle());
                    return true;
                } catch(\PHPUnit_Framework_AssertionFailedError $e) {
                    return false;
                }
            }))
            ->getMock()
            ->shouldReceive('dispatch')
            ->once()
            ->with(RequestEvents::PRE_SEND, Mockery::on(function(HandleEvent $event) use ($testCase, $event3){
                
                try {
                    $testCase->assertSame($event3->getQueue(), $event->getQueue());
                    $testCase->assertSame($event3->getRequest(), $event->getRequest());
                    $testCase->assertSame($event3->getHandle(), $event->getHandle());
                    return true;
                } catch(\PHPUnit_Framework_AssertionFailedError $e) {
                    return false;
                }
            }))
            ->getMock()
        ;
        
        $this->responseFactory
            ->shouldReceive('registerHandle')
            ->once()
            ->with($handle1, $request1)
            ->getMock()
            ->shouldReceive('registerHandle')
            ->once()
            ->with($handle2, $request2)
            ->getMock()
            ->shouldReceive('registerHandle')
            ->once()
            ->with($handle3, $request3)
            ->getMock()
        ;
        $this->multiHandle
            ->shouldReceive('execute')
            ->once()
            ->ordered()
            ->getMock()
            ->shouldReceive('select')
            ->once()
            ->ordered()
            ->getMock()
            ->shouldReceive('getFinishedHandles')
            ->once()
            ->ordered()
            ->andReturn(array(Mockery::mock()->shouldReceive('getHandle')->andReturn($handle1)->getMock()))
            ->getMock()
            ->shouldReceive('removeHandle')
            ->once()
            ->ordered()
            ->with($handle1)
            ->getMock()
        ;
        $this->handleMap
            ->shouldReceive('getRequest')
            ->once()
            ->ordered()
            ->with($handle1)
            ->andReturn($request1)
        ;
        $this->responseFactory
            ->shouldReceive('createResponse')
            ->once()
            ->ordered()
            ->with($handle1)
            ->andReturn($response1)
        ;
        $this->dispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->ordered()
            ->with(RequestEvents::COMPLETE, Mockery::on(function(ResponseEvent $event) use ($testCase, $responseEvent1){
                
                try {
                    $testCase->assertSame($responseEvent1->getQueue(), $event->getQueue());
                    $testCase->assertSame($responseEvent1->getRequest(), $event->getRequest());
                    $testCase->assertSame($responseEvent1->getResponse(), $event->getResponse());
                    return true;
                } catch(\PHPUnit_Framework_AssertionFailedError $e) {
                    return false;
                }
            }))
            ->getMock()
        ;
        $this->handleMap
            ->shouldReceive('clear')
            ->once()
            ->ordered()
            ->with($handle1)
        ;
        $this->multiHandle
            ->shouldReceive('getExecutingCount')
            ->once()
            ->ordered()
            ->andReturn(2)
            ->getMock()
        ;
        $this->multiHandle
            ->shouldReceive('select')
            ->once()
            ->ordered()
            ->getMock()
            ->shouldReceive('getFinishedHandles')
            ->once()
            ->ordered()
            ->andReturn(array(
                Mockery::mock()->shouldReceive('getHandle')->andReturn($handle2)->getMock(),
                Mockery::mock()->shouldReceive('getHandle')->andReturn($handle3)->getMock(),
            ))
            ->getMock()
            ->shouldReceive('removeHandle')
            ->once()
            ->ordered()
            ->with($handle2)
            ->getMock()
        ;
        $this->handleMap
            ->shouldReceive('getRequest')
            ->once()
            ->ordered()
            ->with($handle2)
            ->andReturn($request2)
        ;
        $this->responseFactory
            ->shouldReceive('createResponse')
            ->once()
            ->ordered()
            ->with($handle2)
            ->andReturn(null)
        ;
        $this->dispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->ordered()
            ->with(RequestEvents::COMPLETE, Mockery::on(function(ResponseEvent $event) use ($testCase, $responseEvent2){
                
                try {
                    $testCase->assertSame($responseEvent2->getQueue(), $event->getQueue());
                    $testCase->assertSame($responseEvent2->getRequest(), $event->getRequest());
                    $testCase->assertNull($event->getResponse());
                    return true;
                } catch(\PHPUnit_Framework_AssertionFailedError $e) {
                    return false;
                }
            }))
            ->getMock()
        ;
        $this->handleMap
            ->shouldReceive('clear')
            ->once()
            ->ordered()
            ->with($handle2)
        ;
        $this->multiHandle
            ->shouldReceive('removeHandle')
            ->once()
            ->ordered()
            ->with($handle3)
            ->getMock()
        ;
        $this->handleMap
            ->shouldReceive('getRequest')
            ->once()
            ->ordered()
            ->with($handle3)
            ->andReturn($request3)
        ;
        $this->responseFactory
            ->shouldReceive('createResponse')
            ->once()
            ->ordered()
            ->with($handle3)
            ->andReturn($response3)
        ;
        $this->dispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->ordered()
            ->with(RequestEvents::COMPLETE, Mockery::on(function(ResponseEvent $event) use ($testCase, $responseEvent3){
                
                try {
                    $testCase->assertSame($responseEvent3->getQueue(), $event->getQueue());
                    $testCase->assertSame($responseEvent3->getRequest(), $event->getRequest());
                    $testCase->assertSame($responseEvent3->getResponse(), $event->getResponse());
                    return true;
                } catch(\PHPUnit_Framework_AssertionFailedError $e) {
                    return false;
                }
            }))
            ->getMock()
        ;
        $this->handleMap
            ->shouldReceive('clear')
            ->once()
            ->ordered()
            ->with($handle3)
        ;
        $this->multiHandle
            ->shouldReceive('getExecutingCount')
            ->once()
            ->ordered()
            ->andReturn(0)
            ->getMock()
        ;
        $this->assertSame(array($response1, $response3), $this->queue->send());
    }
    
    public function testGetConfig()
    {
        $this->assertSame($this->config, $this->queue->getConfig());
    }
    
    public function testAddEventListener()
    {
        $listener = function(){};
        $eventName = 'name';
        $priority = 12;
        $this->dispatcher
            ->shouldReceive('addListener')
            ->once()
            ->with($eventName, $listener, $priority)
        ;
        $this->queue->addEventListener($eventName, $listener, $priority);
    }
    
    public function testAddEventSubscriber()
    {
        $subscriber = Mockery::mock('Symfony\Component\EventDispatcher\EventSubscriberInterface');
        $this->dispatcher
            ->shouldReceive('addSubscriber')
            ->once()
            ->with($subscriber)
        ;
        $this->queue->addEventSubscriber($subscriber);
    }
    
    public function testNotifyHandleEvent()
    {
        $name = 'ewrrewrew';
        $request = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $handle = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface');
        $args = array('dasdas', 'tertrter');
        $testCase = $this;
        $handleEvent = new HandleObserverEvent($this->queue, $request, $handle, $args);
        
        $this->handleMap
            ->shouldReceive('getRequest')
            ->once()
            ->with($handle)
            ->andReturn($request)
        ;
        
        $this->dispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(RequestEvents::HANDLE_EVENT.'.ewrrewrew', Mockery::on(function(HandleObserverEvent $event) use ($testCase, $handleEvent){
                
                $testCase->assertSame($handleEvent->getQueue(), $event->getQueue());
                $testCase->assertSame($handleEvent->getRequest(), $event->getRequest());
                $testCase->assertSame($handleEvent->getHandle(), $event->getHandle());
                $testCase->assertSame($handleEvent->getArgs(), $event->getArgs());
                return true;
            }))
        ;
        $this->queue->notifyHandleEvent($name, $handle, $args);
    }
}
