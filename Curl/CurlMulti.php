<?php
namespace Yjv\HttpQueue\Curl;

class CurlMulti implements CurlMultiInterface
{
    protected static $multiErrors = array(
        CURLM_BAD_HANDLE      => array('CURLM_BAD_HANDLE', 'The passed-in handle is not a valid CURLM handle.'),
        CURLM_BAD_EASY_HANDLE => array('CURLM_BAD_EASY_HANDLE', "An easy handle was not good/valid. It could mean that it isn't an easy handle at all, or possibly that the handle already is in used by this or another multi handle."),
        CURLM_OUT_OF_MEMORY   => array('CURLM_OUT_OF_MEMORY', 'You are doomed.'),
        CURLM_INTERNAL_ERROR  => array('CURLM_INTERNAL_ERROR', 'This can only be returned if libcurl bugs. Please report it to us!')
    );
    protected $resource;
    protected $handles = array();
    
    public function __construct()
    {
        $this->resource = curl_multi_init();
    }
    
    public function __destruct()
    {
        $this->close();
    }
    
    public function addHandle(CurlHandleInterface $handle)
    {
        curl_multi_add_handle($this->resource, $handle->getResource());
        $this->handles[(int)$handle->getResource()] = $handle;
        return $this;
    }
    
    public function removeHandle(CurlHandleInterface $handle)
    {
        curl_multi_remove_handle($this->resource, $handle->getResource());
        unset($this->handles[(int)$handle->getResource()]);
        return $this;
    }
    
    public function execute(&$stillRunning = 0)
    {
        return curl_multi_exec($this->resource, $stillRunning);
    }
    
    public function getHandleResponseContent(CurlHandleInterface $handle)
    {
        return curl_multi_getcontent($handle->getResource());
    }
    
    public function select($selectTimeout = 1.0)
    {
        return curl_multi_select($this->resource, $selectTimeout);
    }
    
    public function getFinishedHandleInformation(&$finishedHandleCount = 0)
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
}
