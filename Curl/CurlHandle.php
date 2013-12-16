<?php
namespace Yjv\HttpQueue\Curl;

use Yjv\HttpQueue\Queue\HandleDelegateInterface;

use Yjv\HttpQueue\Connection\SourceStreamInterface;

use Yjv\HttpQueue\Connection\DestinationStreamInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class CurlHandle implements ConnectionHandleInterface
{
    protected $resource;
    protected $options = array();
    protected $delegate;
    
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
        curl_copy_handle($this->resource);
    }
    
    public function getResource()
    {
        return $this->resource;
    }
    
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
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
    
    public function execute()
    {
        return curl_exec($this->resource);
    }
    
    public function getLastTransferInfo($option = 0)
    {
        return curl_getinfo($this->resource, $option);
    }
    
    public function close()
    {
        curl_close($this->resource);
        return $this;
    }
    
    public function setDestinationStream(DestinationStreamInterface $destinationStream)
    {
        $this->setOption(CURLOPT_WRITEFUNCTION, function (CurlHandle $handle, $data) use ($destinationStream)
        {
            return $destinationStream->writeStream($data);
        });
        
        return $this;
    }
    
    public function setSourceStream(SourceStreamInterface $sourceStream)
    {
        $this->setOptions(array(
            CURLOPT_UPLOAD => true,
            CURLOPT_READFUNCTION => function (CurlHandle $handle, $fd, $amountOfDataToRead) use ($sourceStream)
            {
                return $sourceStream->readStream($amountOfDataToRead);
            }
        ));
        
        return $this;
    }
    
    public function setSourcePayload(SourcePayloadInterface $sourcePayload)
    {
        $sourcePayload->setHandle($this);
        $this->setOption(CURLOPT_POSTFIELDS, $sourcePayload->getPayloadContent());
        return $this;
    }
    
    public function setDelegate(HandleDelegateInterface $delegate)
    {
        $this->delegate = $delegate;
        return $this;
    }
    
    public function getDelegate()
    {
        return $this->delegate;
    }
    
    protected function wrapCallbacks(array $options)
    {
        $curlObject = $this;
        
        foreach (CurlEvents::getCallbackEvents() as $callbackOption) {
            
            if (isset($options[$callbackOption])) {
                
                $internalFunction = $options[$callbackOption];
                $options[$callbackOption] = function() use ($internalFunction, $curlObject, $callbackOption)
                {
                    $args = func_get_args();
                    array_shift($args);
                    $curlObject->getDelegate()->handleEvent(
                        CurlEvents::getCallbackEvents($callbackOption), 
                        $curlObject, 
                        $args
                    );
                    array_unshift($args, $curlObject);
                    return call_user_func_array($internalFunction, $args);
                };
            }
        }
        
        return $options;
    }
}
