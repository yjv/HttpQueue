<?php
namespace Yjv\HttpQueue\Curl;

use Yjv\HttpQueue\Payload\SourceStreamInterface;

use Yjv\HttpQueue\Payload\DestinationStreamInterface;

use Yjv\HttpQueue\Payload\StreamSourcePayloadInterface;

use Yjv\HttpQueue\Payload\StreamDestinationPayloadInterface;

use Yjv\HttpQueue\Payload\SourcePayloadInterface;

use Yjv\HttpQueue\Payload\DestinationPayloadInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class CurlHandle implements ConnectionHandleInterface
{
    protected $resource;
    protected $options = array();
    
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
        $this->setOption(CURLOPT_POSTFIELDS, $sourcePayload->getPayloadContent());
        
        
        
        return $this;
    }
    
    protected function wrapCallbacks(array $options)
    {
        $curlObject = $this;
        
        $callbackOptions = array(
            CURLOPT_WRITEFUNCTION,
            CURLOPT_READFUNCTION,
            CURLOPT_HEADERFUNCTION,
            CURLOPT_PROGRESSFUNCTION
        );
        
        if (defined('CURLOPT_PASSWDFUNCTION')) {
            
            $callbackOptions[] = CURLOPT_PASSWDFUNCTION;
        }
        
        foreach ($callbackOptions as $callbackOption) {
            
            if (isset($options[$callbackOption])) {
                
                $internalFunction = $options[$callbackOption];
                $options[$callbackOption] = function() use ($internalFunction, $curlObject)
                {
                    $args = func_get_args();
                    $args[0] = $curlObject;
                    return call_user_func_array($internalFunction, $args);
                };
            }
        }
        
        return $options;
    }
}
