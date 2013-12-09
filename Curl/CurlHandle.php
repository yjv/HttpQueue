<?php
namespace Yjv\HttpQueue\Curl;

class CurlHandle implements CurlHandleInterface
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
        $this->setOptions(array($name => $value));
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
    
    protected function wrapCallbacks(array $options)
    {
        $curlObject = $this;
        
        $callbackFunctions = array(
            CURLOPT_WRITEFUNCTION,
            CURLOPT_READFUNCTION,
            CURLOPT_HEADERFUNCTION,
//             CURLOPT_PASSWDFUNCTION,
            CURLOPT_PROGRESSFUNCTION
        );
        
        foreach ($callbackFunctions as $callbackFunction) {
            
            if (isset($options[$callbackFunction])) {
                
                $internalFunction = $options[$callbackFunction];
                $options[$callbackFunction] = function() use ($internalFunction, $curlObject)
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
