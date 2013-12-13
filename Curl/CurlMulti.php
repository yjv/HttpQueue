<?php
namespace Yjv\HttpQueue\Curl;

use Yjv\HttpQueue\Connection\ConnectionInterface;

use Yjv\HttpQueue\Connnection\MultiConnectionInterface;

class CurlMulti implements MultiConnectionInterface
{
    protected static $multiErrors = array(
        CURLM_BAD_HANDLE      => array('CURLM_BAD_HANDLE', 'The passed-in handle is not a valid CURLM handle.'),
        CURLM_BAD_EASY_HANDLE => array('CURLM_BAD_EASY_HANDLE', "An easy handle was not good/valid. It could mean that it isn't an easy handle at all, or possibly that the handle already is in used by this or another multi handle."),
        CURLM_OUT_OF_MEMORY   => array('CURLM_OUT_OF_MEMORY', 'You are doomed.'),
        CURLM_INTERNAL_ERROR  => array('CURLM_INTERNAL_ERROR', 'This can only be returned if libcurl bugs. Please report it to us!')
    );
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
    
    public function addConnection(ConnectionInterface $handle)
    {
        curl_multi_add_handle($this->resource, $handle->getResource());
        $this->handles[(int)$handle->getResource()] = $handle;
        $this->ignoreTimeout = true;
        return $this;
    }
    
    public function removeConnection(ConnectionInterface $handle)
    {
        curl_multi_remove_handle($this->resource, $handle->getResource());
        unset($this->handles[(int)$handle->getResource()]);
        return $this;
    }
    
    public function execute(&$stillRunning = 0)
    {
        return $this->checkExecuteResult(curl_multi_exec($this->resource, $stillRunning));
    }
    
    public function getConnectionResponseContent(ConnectionInterface $handle)
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
    
    public function getFinishedConnectionInformation(&$finishedHandleCount = 0)
    {
        $info = curl_multi_info_read($this->resource, $finishedHandleCount);
        
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
