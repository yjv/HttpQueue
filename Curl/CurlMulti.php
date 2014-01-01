<?php
namespace Yjv\HttpQueue\Curl;

use Yjv\HttpQueue\Connection\FinishedHandleInformation;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Connection\MultiHandleInterface;

class CurlMulti implements MultiHandleInterface
{
    const STATUS_OK = CURLM_OK;
    const STATUS_PERFORMING = CURLM_CALL_MULTI_PERFORM;
    
    protected $resource;
    protected $handles = array();
    protected $firstSelectCall = true;
    
    public function __construct()
    {
        $this->initialize();
    }
    
    public function __destruct()
    {
        if (is_resource($this->resource)) {

            $this->close();
        }
    }
    
    public function __clone()
    {
        $this->initialize();
    }
    
    public function addHandle(ConnectionHandleInterface $handle)
    {
        curl_multi_add_handle($this->resource, $handle->getResource());
        $this->handles[(int)$handle->getResource()] = $handle;
        $this->firstSelectCall = true;
        return $this;
    }
    
    public function removeHandle(ConnectionHandleInterface $handle)
    {
        curl_multi_remove_handle($this->resource, $handle->getResource());
        unset($this->handles[(int)$handle->getResource()]);
        return $this;
    }
    
    public function execute()
    {
        $stillRunning = 0;
        while($this->checkExecuteResult(curl_multi_exec($this->resource, $stillRunning)));
        return true;
    }
    
    public function getHandleResponseContent(ConnectionHandleInterface $handle)
    {
        return curl_multi_getcontent($handle->getResource());
    }
    
    public function getStillRunningCount()
    {
        $stillRunning = 0;
        curl_multi_exec($this->resource, $stillRunning);
        return $stillRunning;
    }
    
    public function select($timeout = 1.0)
    {
        // The first curl_multi_select often times out no matter what, but is usually required for fast transfers
        if ($this->firstSelectCall) {
            
            $oldTimeout = $timeout;
            $timeout = 0.001;
        }
        
        $result = curl_multi_select($this->resource, $timeout);
        
        if($result == -1 && $this->firstSelectCall) {
            
            // Perform a usleep if a select returns -1: https://bugs.php.net/bug.php?id=61141
            usleep(150);
            $stillRunning = 0;
            curl_multi_exec($this->resource, $stillRunning);
            $result = curl_multi_select($this->resource, $oldTimeout - $timeout);
            $this->firstSelectCall = false;
        }
        
        return $result == -1 ? 0 : $result;
    }
    
    public function getFinishedHandles()
    {
        $finishedHandles = array();
        
        while($info = curl_multi_info_read($this->resource)) {
            
            $finishedHandles[] = new FinishedHandleInformation(
                $this->handles[(int)$info['handle']], 
                $info['result'], 
                $info['msg']
            );
        }
        
        return $finishedHandles;
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
        if ($executeResultCode == self::STATUS_PERFORMING) {
    
            return true;
        }
    
        if ($executeResultCode == self::STATUS_OK) {
    
            return false;
        }
    
        throw new CurlMultiException($executeResultCode);
    }
    
    protected function initialize()
    {
        $this->resource = curl_multi_init();
        $this->handles = array();
        $this->firstSelectCall = true;
    }
}
