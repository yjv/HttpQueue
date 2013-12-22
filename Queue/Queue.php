<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Response\ResponseEvent;

use Yjv\HttpQueue\Connection\HandleObserverInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Connnection\MultiConnectionInterface;

use Yjv\HttpQueue\Response\RecieveStatusLineEvent;

use Yjv\HttpQueue\Response\ResponseEvents;

use Yjv\HttpQueue\Request\RequestMediatorInterface;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

use Yjv\HttpQueue\Curl\CurlMultiInterface;

use Yjv\HttpQueue\Curl\CurlMultiException;

use Yjv\HttpQueue\Curl\CurlMulti;

use Yjv\HttpQueue\Response\Response;

use Yjv\HttpQueue\RequestResponseConnectionMap;

use Yjv\HttpQueue\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Request\RequestEvent;

use Yjv\HttpQueue\Request\RequestEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Queue implements QueueInterface, HandleObserverInterface
{
    protected $pending;
    protected $responses;
    protected $config;
    protected $sendCalls = 0;
    
    public function __construct(QueueConfigInterface $config)
    {
        $this->pending = new \SplObjectStorage();
        $this->responses = new \SplObjectStorage();
        $this->config = $config;
    }
    
    public function queue(RequestInterface $request)
    {
        $this->pending->attach($request);
        return $this;
    }
    
    public function unqueue(RequestInterface $request)
    {
        $this->pending->detach($request);
        return $this;
    }
    
    public function send(RequestInterface $request = null)
    {
        $this->sendCalls++;
        
        if ($request) {
            
            $this->queue($request);
        }

        $this->queuePendingRequests();
        $this->executeHandles();   

        $this->sendCalls--;
        
        if (!$this->sendCalls) {
            
            $responses = iterator_to_array($this->responses);
            $this->responses->removeAll($this->responses);
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
            RequestEvents::HANDLE_EVENT, 
            new HandleEvent(
                $this, 
                $this->config->getHandleMap()->getRequest($handle), 
                $handle, 
                $name, 
                $args
            )
        );
    }

    protected function queuePendingRequests()
    {
        foreach ($this->pending as $pending) {
            
            $handle = $this->config->getHandleFactory()->createHandle($pending);
            $handle->setObserver($this);
            $this->config->getHandleMap()->setRequest($handle, $pending);
            $this->pending->detach($pending);
            $this->config->getResponseFactory()->registerHandle($handle, $pending);
            $this->config->getMultiHandle()->addHandle($handle);
        }
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
            $this->config->getMultiHandle()->select(1);
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
            $handle->close();
            $request = $this->config->getHandleMap()->getRequest($handle);
            $response = $this->config->getResponseFactory()->createResponse($handle);
            
            if ($response) {

                $event = new ResponseEvent($this, $request, $response);
                $this->config->getEventDispatcher()->dispatch(RequestEvents::COMPLETE, $event);
                $this->responses->attach($response);
            }
            
            $this->config->getHandleMap()->clear($handle);
        }
    }
}
