<?php
namespace Yjv\HttpQueue\Curl;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Connnection\MultiHandleInterface;

class CurlMulti implements MultiHandleInterface
{
    protected $resource;
    protected $handles = array();
    protected $ignoreTimeout = true;
    
    public function __construct()
    {
        $this->resource = curl_multi_init();
    }
    
    public function __destruct()
    {
        $this->close();
    }
    
    public function addConnection(ConnectionHandleInterface $handle)
    {
        curl_multi_add_handle($this->resource, $handle->getResource());
        $this->handles[(int)$handle->getResource()] = $handle;
        $this->ignoreTimeout = true;
        return $this;
    }
    
    public function removeConnection(ConnectionHandleInterface $handle)
    {
        curl_multi_remove_handle($this->resource, $handle->getResource());
        unset($this->handles[(int)$handle->getResource()]);
        return $this;
    }
    
    public function execute(&$stillRunning = 0)
    {
        return $this->checkExecuteResult(curl_multi_exec($this->resource, $stillRunning));
    }
    
    public function getHandleResponseContent(ConnectionHandleInterface $handle)
    {
        return curl_multi_getcontent($handle->getResource());
    }
    
    public function select($selectTimeout = 1.0)
    {
        if ($this->ignoreTimeout) {
            
            $selectTimeout = 0.001;
            $this->ignoreTimeout = false;
        }
        
        if(($result = curl_multi_select($this->resource, $selectTimeout)) == -1) {
            
            // Perform a usleep if a select returns -1: https://bugs.php.net/bug.php?id=61141
            usleep(150);
        }
        
        return $result == -1 ? 0 : $result;
    }
    
    public function getFinishedConnectionInformation(&$finishedConnectionCount = 0)
    {
        $info = curl_multi_info_read($this->resource, $finishedConnectionCount);
        
        if ($info) {
            
            $info = new FinishedHandleInformation(
                $this->handles[(int)$info['handle']], 
                $info['result'], 
                $info['msg']
            );
        }
        
        return $info;
    }
    
    public function close()
    {
        curl_multi_close($this->resource);
        return $this;
    }
    
    public function getResource()
    {
        return $this->resource;
    }
    
    /**
     * Throw an exception for a cURL multi response if needed
     *
     * @param int $code Curl response code
     * @throws CurlException
     */
    protected function checkExecuteResult($executeResultCode)
    {
        if ($executeResultCode == CurlMultiInterface::STATUS_PERFORMING) {
    
            return true;
        }
    
        if ($executeResultCode == CurlMultiInterface::STATUS_OK) {
    
            return false;
        }
    
        throw new CurlMultiException($executeResultCode);
    }
}
