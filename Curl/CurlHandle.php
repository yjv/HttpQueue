<?php
namespace Yjv\HttpQueue\Curl;

use Yjv\HttpQueue\Transport\Payload\StreamDestinationInterface;
use Yjv\HttpQueue\Transport\HandleObserverInterface;
use Yjv\HttpQueue\Transport\Payload\PayloadSourceInterface;
use Yjv\HttpQueue\Transport\Payload\StreamSourceInterface;
use Yjv\HttpQueue\Transport\Payload\PayloadDestinationInterface;
use Yjv\HttpQueue\Transport\HandleInterface;

class CurlHandle implements HandleInterface
{
    protected $resource;
    protected $options = array();
    protected $observer;
    
    public function __construct()
    {
        $this->resource = curl_init();
    }
    
    public function __destruct()
    {
        if (is_resource($this->resource)) {

            $this->close();
        }
    }
    
    public function __clone()
    {
        $this->resource = curl_copy_handle($this->resource);
    }
    
    public function getResource()
    {
        return $this->resource;
    }
    
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            
            $this->options[$key] = $value;
        }
        curl_setopt_array($this->resource, $this->wrapCallbacks($options));
        return $this;
    }
    
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        $wrappedCallbacks = $this->wrapCallbacks(array($name => $value));
        curl_setopt($this->resource, $name, $wrappedCallbacks[$name]);
        return $this;
    }
    
    public function getOptions()
    {
        return $this->options;
    }
    
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }
    
    public function execute()
    {
        return curl_exec($this->resource);
    }
    
    public function getLastTransferInfo($option = null)
    {
        if (!is_null($option)) {
            
            return curl_getinfo($this->resource, $option);
        }
        
        return curl_getinfo($this->resource);
    }
    
    public function close()
    {
        curl_close($this->resource);
        return $this;
    }
    
    public function setStreamDestination(StreamDestinationInterface $streamDestination)
    {
        $streamDestination->setSourceHandle($this);
        
        $this->setOption(CURLOPT_WRITEFUNCTION, function (CurlHandle $handle, $data) use ($streamDestination)
        {
            return $streamDestination->writeStream($data);
        });
        
        return $this;
    }
    
    public function setStreamSource(StreamSourceInterface $streamSource)
    {
        $streamSource->setDestinationHandle($this);
        
        $this->setOptions(array(
            CURLOPT_UPLOAD => true,
            CURLOPT_READFUNCTION => function (CurlHandle $handle, $fd, $amountOfDataToRead) use ($streamSource)
            {
                return $streamSource->readStream($amountOfDataToRead);
            }
        ));
        
        return $this;
    }
    
    public function setObserver(HandleObserverInterface $observer)
    {
        $this->observer = $observer;
        return $this;
    }
    
    public function getObserver()
    {
        return $this->observer;
    }
    
    protected function wrapCallbacks(array $options)
    {
        $curlObject = $this;
        
        foreach (CurlEvents::getCallbackEvents() as $callbackOption => $eventName) {

            if (isset($options[$callbackOption])) {
                
                $internalFunction = $options[$callbackOption];
                $options[$callbackOption] = function() use (
                    $internalFunction, 
                    $curlObject, 
                    $callbackOption, 
                    $eventName
                ) {
                    $args = func_get_args();
                    array_shift($args);
                    if ($curlObject->getObserver()) {
                        $curlObject->getObserver()->handleEvent(
                            $eventName, 
                            $curlObject, 
                            $args
                        );
                    }
                    array_unshift($args, $curlObject);
                    return call_user_func_array($internalFunction, $args);
                };
            }
        }
        
        return $options;
    }
}
