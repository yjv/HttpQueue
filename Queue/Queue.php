<?php
namespace Yjv\HttpRequest\Queue;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Queue implements QueueInterface
{
    protected $pending;
    protected $sending;
    protected $handleRequestHash = array();
    protected $curlMulti;
    protected $dispatcher;
    
    public function __construct(EventDispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher ?: new EventDispatcher();
        $this->pending = new \SplObjectStorage();
        $this->sending = new \SplObjectStorage();
        $this->finished = new \SplObjectStorage();
        $this->curlMulti = curl_multi_init();
    }
    
    public function __destruct()
    {
        curl_multi_close($this->curlMulti);
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
        if ($request) {
            
            $this->queue($request);
        }
        
        foreach ($this->pending as $pending) {
            
            $this->pending->detach($pending);
            $this->sending->attach($pending);
            $handle = $pending->getHandle();
            $this->handleRequestHash[(int)$handle] = $pending;
            curl_multi_add_handle($this->curlMulti, $handle);
        }
        
        // execute the handles
        do {
            $result = curl_multi_exec($this->curlMulti, $running);
        } while($result === CURLM_CALL_MULTI_PERFORM);
        
        while (count($this->sending)) {
            
            curl_multi_select($this->curlMulti);
            
            while ($info = curl_multi_info_read($this->curlMulti)) {
            
                $request = $this->sending[(int)$info['handle']];
                $result = $info['result'];
                
                curl_multi_remove_handle($this->curlMulti, $request->getHandle());
                unset($this->sending[(int)$request->getHandle()]);
                
                $response = new Response(curl_multi_getcontent($request->getHandle()));
                if ($result !== CURLE_OK) {
                    $event->response->setError(new Error(curl_error($request->getHandle()), $result));
                }
                $event = new RequestEvent($request, $queue, $response);
                $this->dispatcher->dispatch(RequestEvents::COMPLETE, $event);
            }
        }
        
        return;        
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
    
    /**
     * Execute and select curl handles
     */
    protected function executeHandles()
    {
        // The first curl_multi_select often times out no matter what, but is usually required for fast transfers
        $selectTimeout = 0.001;
        $active = false;
        do {
            while (($mrc = curl_multi_exec($this->multiHandle, $active)) == CURLM_CALL_MULTI_PERFORM);
            $this->checkCurlResult($mrc);
            $this->processMessages();
            if ($active && curl_multi_select($this->multiHandle, $selectTimeout) === -1) {
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
    protected function checkCurlResult($code)
    {
        if ($code != CURLM_OK && $code != CURLM_CALL_MULTI_PERFORM) {
            throw new CurlException(isset($this->multiErrors[$code])
                    ? "cURL error: {$code} ({$this->multiErrors[$code][0]}): cURL message: {$this->multiErrors[$code][1]}"
                    : 'Unexpected cURL error: ' . $code
            );
        }
    }
        
    /**
     * Process any received curl multi messages
     */
    protected function processMessages()
    {
        while ($done = curl_multi_info_read($this->multiHandle)) {
            $request = $this->handleRequestHash[(int)$done['handle']];
            $result = $info['result'];
            
            curl_multi_remove_handle($this->curlMulti, $request->getHandle());
            unset($this->sending[(int)$request->getHandle()]);
            
            $response = new Response(curl_multi_getcontent($request->getHandle()));
            if ($result !== CURLE_OK) {
                $event->response->setError(new Error(curl_error($request->getHandle()), $result));
            }
            $event = new RequestEvent($request, $queue, $response);
            $this->dispatcher->dispatch(RequestEvents::COMPLETE, $event);
        }
    }
}
