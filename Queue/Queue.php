<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Response\RecieveStatusLineEvent;

use Yjv\HttpQueue\Response\ResponseEvents;

use Yjv\HttpQueue\Request\RequestMediatorInterface;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

use Yjv\HttpQueue\Curl\CurlMultiInterface;

use Yjv\HttpQueue\Curl\CurlMultiException;

use Yjv\HttpQueue\Curl\CurlMulti;

use Yjv\HttpQueue\Response\Response;

use Yjv\HttpQueue\RequestResponseHandleMap;

use Yjv\HttpQueue\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Request\RequestEvent;

use Yjv\HttpQueue\Request\RequestEvents;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Queue implements QueueInterface
{
    protected $pending;
    protected $responses;
    protected $handleMap;
    protected $curlMulti;
    protected $dispatcher;
    protected $sendCalls = 0;
    protected $requestMediator;
    
    public function __construct(
        CurlMultiInterface $curlMulti = null, 
        EventDispatcherInterface $dispatcher = null,
        RequestMediatorInterface $requestMediator = null
    ) {
        $this->pending = new \SplObjectStorage();
        $this->handleMap = new RequestResponseHandleMap();
        $this->responses = new \SplObjectStorage();
        $this->curlMulti = $curlMulti ?: new CurlMulti();
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
        $this->requestMediator = $requestMediator ?: new RequestMediator();
        $this->requestMediator->setHandleMap($this->handleMap);
        $this->requestMediator->setDispatcher($this->dispatcher);
        $this->requestMediator->setQueue($this);
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
    
    public function getCurlMulti()
    {
        return $this->curlMulti;
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

    protected function queuePendingRequests()
    {
        foreach ($this->pending as $pending) {
            
            $pending->setRequestMediator($this->requestMediator);
            $handle = $pending->createHandle()
                ->setOption(CURLOPT_WRITEFUNCTION, array($this->requestMediator, 'writeResponseBody'))
                ->setOption(CURLOPT_HEADERFUNCTION, array($this->requestMediator, 'writeResponseHeader'))
            ;
            
            $this->handleMap->setRequest($handle, $pending);
            $this->curlMulti->addHandle($handle);
            $this->pending->detach($pending);
        }
    }
    
    /**
     * Execute and select curl handles
     */
    protected function executeHandles()
    {
        // The first curl_multi_select often times out no matter what, but is usually required for fast transfers
        $selectTimeout = 0.001;
        $active = false;
        do {
            while ($this->checkCurlMultiExecuteResult($this->curlMulti->execute($active)));
            
            $this->processResults();
            if ($active && $this->curlMulti->select($selectTimeout) === -1) {
                // Perform a usleep if a select returns -1: https://bugs.php.net/bug.php?id=61141
                usleep(150);
            }
            $selectTimeout = 1;
        } while ($active);
    }
    
    /**
     * Throw an exception for a cURL multi response if needed
     *
     * @param int $code Curl response code
     * @throws CurlException
     */
    protected function checkCurlMultiExecuteResult($executeResultCode)
    {
        if ($executeResultCode == CurlMultiInterface::STATUS_PERFORMING) {
            
            return true;
        }
        
        if ($executeResultCode == CurlMultiInterface::STATUS_OK) {
            
            return false;
        }
        
        throw new CurlMultiException($executeResultCode);
    }
    
   /**
    * Process any received finished requests
    */
    protected function processResults()
    {
        while ($done = $this->curlMulti->getFinishedHandleInformation()) {

            $handle = $done->getHandle();
            $this->curlMulti->removeHandle($handle);
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
