<?php
namespace Yjv\HttpQueue\Queue;

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

class Queue implements QueueInterface, HandleDelegateInterface
{
    protected $pending;
    protected $responses;
    protected $handleMap;
    protected $multiConnection;
    protected $dispatcher;
    protected $sendCalls = 0;
    protected $requestMediator;
    
    public function __construct(
        MultiConnectionInterface $multiConnection = null, 
        EventDispatcherInterface $dispatcher = null
    ) {
        $this->pending = new \SplObjectStorage();
        $this->handleMap = new RequestResponseConnectionMap();
        $this->responses = new \SplObjectStorage();
        $this->multiConnection = $multiConnection ?: new CurlMulti();
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
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
        $this->executeConections();   

        $this->sendCalls--;
        
        if (!$this->sendCalls) {
            
            $responses = iterator_to_array($this->responses);
            $this->responses->removeAll($this->responses);
            return $responses;
        }
    }
    
    public function getMultiConnection()
    {
        return $this->multiConnection;
    }
    
    public function addEventListener($eventName, $listener, $priority = 0)
    {
        $this->dispatcher->addListener($eventName, $listener, $priority);
        return $this;
    }
    
    public function addEventSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->addSubscriber($subscriber);
        return $this;
    }
    
    public function getResponse(RequestInterface $request)
    {
        if (!isset($this->responses[$request])) {
            
            return array();
        }
        
        return $this->responses[$request];
    }
    
    public function getHandleMap()
    {
        return $this->handleMap;
    }
    
    public function handleEvent($name, ConnectionHandleInterface $handle, array $args)
    {
        $this->dispatcher->dispatch(QueueEvents::HANDLE_EVENT, new HandleEvent($name, $handle, $args));
    }

    protected function queuePendingRequests()
    {
        foreach ($this->pending as $pending) {
            
            $handle = $pending->createHandle()
            ;
            
            $this->handleMap->setRequest($handle, $pending);
            $this->multiConnection->addConnection($handle);
            $this->pending->detach($pending);
        }
    }
    
    /**
     * Execute and select curl handles
     */
    protected function executeConnections()
    {
        // The first curl_multi_select often times out no matter what, but is usually required for fast transfers
        $active = false;
        do {
            while ($this->multiConnection->execute($active));
            
            $this->processResults();
            $this->multiConnection->select(1);
        } while ($active);
    }
    
   /**
    * Process any received finished requests
    */
    protected function processResults()
    {
        while ($done = $this->multiConnection->getFinishedConnectionInformation()) {

            $handle = $done->getHandle();
            $this->multiConnection->removeConnection($handle);
            $handle->close();
            $request = $this->handleMap->getRequest($handle);
            $response = $this->handleMap->getResponse($handle);
            
            $event = new RequestEvent($this, $request, $response);
    
            if ($done->getResult() !== CurlMultiInterface::STATUS_OK) {
        
                $this->dispatcher->dispatch(RequestEvents::CURL_ERROR, $event);
            }
    
            $this->dispatcher->dispatch(RequestEvents::COMPLETE, $event);
            
            if ($response) {

                $this->responses->attach($response);
            }
            
            $this->handleMap->clear($handle);
        }
    }
}
