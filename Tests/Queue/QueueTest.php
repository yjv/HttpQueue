<?php
namespace Yjv\HtpQueue\Tests\Queue;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

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
        
        $handle2 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface')
            ->shouldReceive('setObserver')
            ->once()
            ->with($this->queue)
            ->getMock()
        ;
        $request2 = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        
        $handle3 = Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface')
            ->shouldReceive('setObserver')
            ->once()
            ->with($this->queue)
            ->getMock()
        ;
        $request3 = Mockery::mock('Yjv\HttpQueue\Request\RequestInterface');
        $response3 = Mockery::mock('Yjv\HttpQueue\Response\ResponseInterface');
        
        $this->handleMap
            ->shouldReceive('getHandles')
            ->once()
            ->andReturn(array($handle1, $handle2, $handle3))
            ->getMock()
        ;
        
        $this->setupPresendExpectation($handle1, $request1);
        $this->setupPresendExpectation($handle2, $request2);
        $this->setupPresendExpectation($handle3, $request3);
        $this->multiHandle
            ->shouldReceive('execute')
            ->once()
            ->ordered()
            ->getMock()
        ;
        $this->setupSelectLoop(array($handle1), array($request1), array($response1), 2);
        $this->setupSelectLoop(array($handle2, $handle3), array($request2, $request3), array(null, $response3), 0);
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
    
    protected function setupSelectLoop(array $handles, array $requests, array $responses, $handlesLeft)
    {
        $testCase = $this;
        $this->multiHandle
            ->shouldReceive('select')
            ->once()
            ->ordered()
            ->getMock()
            ->shouldReceive('getFinishedHandles')
            ->once()
            ->ordered()
            ->andReturn(array_map(function($handle){
                return Mockery::mock()->shouldReceive('getHandle')->andReturn($handle)->getMock();
            }, $handles))
            ->getMock()
        ;
            
        foreach ($handles as $key => $handle) {
            
            $request = $requests[$key];
            $response = $responses[$key];
            $responseEvent = new ResponseEvent($this->queue, $request, $response);
            
            $this->multiHandle
                ->shouldReceive('removeHandle')
                ->once()
                ->ordered()
                ->with($handle)
                ->getMock()
            ;
        
            $this->handleMap
                ->shouldReceive('getRequest')
                ->once()
                ->ordered()
                ->with($handle)
                ->andReturn($request)
            ;
            $this->responseFactory
                ->shouldReceive('createResponse')
                ->once()
                ->ordered()
                ->with($handle)
                ->andReturn($response)
            ;
            $this->dispatcher
                ->shouldReceive('dispatch')
                ->once()
                ->ordered()
                ->with(RequestEvents::COMPLETE, Mockery::on(function(ResponseEvent $event) use ($testCase, $responseEvent){
                    
                    try {
                        $testCase->assertSame($responseEvent->getQueue(), $event->getQueue());
                        $testCase->assertSame($responseEvent->getRequest(), $event->getRequest());
                        $testCase->assertSame($responseEvent->getResponse(), $event->getResponse());
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
                ->with($handle)
            ;
        }
        $this->multiHandle
            ->shouldReceive('getExecutingCount')
            ->once()
            ->ordered()
            ->andReturn($handlesLeft)
            ->getMock()
        ;
    }
    
    protected function setupPresendExpectation(ConnectionHandleInterface $handle, RequestInterface $request)
    {
        $testCase = $this;
        $this->handleMap
            ->shouldReceive('getRequest')
            ->once()
            ->with($handle)
            ->andReturn($request)
            ->getMock()
        ;
        
        $event = new HandleEvent($this->queue, $request, $handle);
        $this->dispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(RequestEvents::PRE_SEND, Mockery::on(function(HandleEvent $passedEvent) use ($testCase, $event){
                
                try {
                    $testCase->assertSame($event->getQueue(), $passedEvent->getQueue());
                    $testCase->assertSame($event->getRequest(), $passedEvent->getRequest());
                    $testCase->assertSame($event->getHandle(), $passedEvent->getHandle());
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
            ->with($handle, $request)
            ->getMock()
        ;
    }
}
