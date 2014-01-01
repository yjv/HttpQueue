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
        foreach ($this->pending as $pending) {
            
            $event = new HandleEvent($this, $pending);
            $this->config->getEventDispatcher()->dispatch(
                RequestEvents::PRE_CREATE_HANDLE, 
                $event
            );
            $pending = $event->getRequest();
            
            if (!($handle = $event->getHandle())) {
                
                $handle = $this->config->getHandleFactory()->createHandle($pending);
            }
            
            $event = new HandleEvent($this, $pending, $handle);
            $this->config->getEventDispatcher()->dispatch(
                RequestEvents::POST_CREATE_HANDLE, 
                $event
            );
            $handle = $event->getHandle();
            $handle->setObserver($this);
            $this->config->getHandleMap()->setRequest($handle, $pending);
            $this->config->getResponseFactory()->registerHandle($handle, $pending);
            $this->config->getMultiHandle()->addHandle($handle);
        }
        
        $this->pending = array();
    }
    
    /**
     * Execute and select curl handles
     */
    protected function executeHandles()
    {
        // The first curl_multi_select often times out no matter what, but is usually required for fast transfers
        $active = false;
        do {
            while ($this->config->getMultiHandle()->execute($active));
            
            $this->processResults();
            $this->config->getMultiHandle()->select();
        } while ($active);
    }
    
   /**
    * Process any received finished requests
    */
    protected function processResults()
    {
        while ($done = $this->config->getMultiHandle()->getFinishedHandleInformation()) {

            $handle = $done->getHandle();
            $this->config->getMultiHandle()->removeHandle($handle);
            $request = $this->config->getHandleMap()->getRequest($handle);
            $response = $this->config->getResponseFactory()->createResponse($handle);
            
            if ($response) {

                $event = new ResponseEvent($this, $request, $response);
                $this->config->getEventDispatcher()->dispatch(
                    RequestEvents::COMPLETE, 
                    $event
                );
                $this->responses[] = $event->getResponse();
            }
            
            $this->config->getHandleMap()->clear($handle);
        }
    }
}
