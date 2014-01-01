<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Event\ResponseEvent;

use Yjv\HttpQueue\Event\HandleObserverEvent;

use Yjv\HttpQueue\Event\HandleEvent;

use Yjv\HttpQueue\Event\CreateHandleEvent;

use Yjv\HttpQueue\Event\RequestHandleEvent;

use Yjv\HttpQueue\Connection\HandleObserverInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Connnection\MultiConnectionInterface;

use Yjv\HttpQueue\Request\RequestMediatorInterface;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

use Yjv\HttpQueue\Curl\CurlMultiInterface;

use Yjv\HttpQueue\Curl\CurlMultiException;

use Yjv\HttpQueue\Curl\CurlMulti;

use Yjv\HttpQueue\Response\Response;

use Yjv\HttpQueue\RequestResponseConnectionMap;

use Yjv\HttpQueue\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Event\RequestEvent;

use Yjv\HttpQueue\Event\RequestEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Queue implements QueueInterface, HandleObserverInterface
{
    protected $pending = array();
    protected $responses = array();
    protected $config;
    protected $sendCalls = 0;
    
    public function __construct(QueueConfigInterface $config)
    {
        $this->config = $config;
    }
    
    public function queue(RequestInterface $request)
    {
        $this->pending[] = $request;
        return $this;
    }
    
    public function unqueue(RequestInterface $request)
    {
        if(($index = array_search($request, $this->pending)) !== false)
        {
            unset($this->pending[$index]);
        }
        
        return $this;
    }
    
    public function send()
    {
        $this->sendCalls++;
        $this->queuePendingRequests();
        $this->executeHandles();   
        $this->sendCalls--;
        
        if (!$this->sendCalls) {
            
            $responses = $this->responses;
            $this->responses = array();
            return $responses;
        }
    }
    
    public function getConfig()
    {
        return $this->config;
    }
    
    public function addEventListener($eventName, $listener, $priority = 0)
    {
        $this->config->getEventDispatcher()->addListener($eventName, $listener, $priority);
        return $this;
    }
    
    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->config->getEventDispatcher()->addSubscriber($subscriber);
        return $this;
    }
    
    public function handleEvent($name, ConnectionHandleInterface $handle, array $args)
    {
        $this->config->getEventDispatcher()->dispatch(
            RequestEvents::HANDLE_EVENT.'.'.$name, 
            new HandleObserverEvent(
                $this, 
                $this->config->getHandleMap()->getRequest($handle), 
                $handle, 
                $args
            )
        );
    }

    protected function queuePendingRequests()
    {
        foreach ($this->pending as $request) {
            
            $event = new HandleEvent($this, $request);
            $this->config->getEventDispatcher()->dispatch(
                RequestEvents::PRE_CREATE_HANDLE, 
                $event
            );
            $request = $event->getRequest();
            
            if (!($handle = $event->getHandle())) {
                
                $handle = $this->config->getHandleFactory()->createHandle($request);
            }
            
            $event = new HandleEvent($this, $request, $handle);
            $this->config->getEventDispatcher()->dispatch(
                RequestEvents::POST_CREATE_HANDLE, 
                $event
            );
            $handle = $event->getHandle();
            $handle->setObserver($this);
            $this->config->getHandleMap()->setRequest($handle, $request);
            $this->config->getResponseFactory()->registerHandle($handle, $request);
            $this->config->getMultiHandle()->addHandle($handle);
        }
        
        $this->pending = array();
    }
    
    /**
     * Execute and select curl handles
     */
    protected function executeHandles()
    {
        $this->config->getMultiHandle()->execute();
        do {
            $this->config->getMultiHandle()->select();
            $this->processResults();
        } while ($this->config->getMultiHandle()->getStillRunningCount());
    }
    
   /**
    * Process any received finished requests
    */
    protected function processResults()
    {
        foreach($this->config->getMultiHandle()->getFinishedHandles() as $finished) {

            $handle = $finished->getHandle();
            $this->config->getMultiHandle()->removeHandle($handle);
            
            $event = new ResponseEvent(
                $this, 
                $this->config->getHandleMap()->getRequest($handle), 
                $this->config->getResponseFactory()->createResponse($handle)
            );
            $this->config->getEventDispatcher()->dispatch(
                RequestEvents::COMPLETE, 
                $event
            );
            
            if ($event->getResponse()) {

                $this->responses[] = $event->getResponse();
            }
            
            $this->config->getHandleMap()->clear($handle);
        }
    }
}
