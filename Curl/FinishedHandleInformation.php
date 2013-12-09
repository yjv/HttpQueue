<?php
namespace Yjv\HttpQueue\Curl;

class FinishedHandleInformation
{
    protected $handle;
    protected $result;
    protected $message;
    
    public function __construct(CurlHandleInterface $handle, $result, $message)
    {
        $this->handle = $handle;
        $this->result = $result;
        $this->message = $message;
    }
    
    public function getHandle()
    {
        return $this->handle;
    }
    
    public function getResult()
    {
        return $this->result;
    }
    
    public function getMessage()
    {
        return $this->message;
    }
}
