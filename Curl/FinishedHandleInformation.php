<?php
namespace Yjv\HttpQueue\Curl;

use Yjv\HttpQueue\Connection\FinishedConnectionInformationInterface;

class FinishedHandleInformation implements FinishedConnectionInformationInterface
{
    protected $handle;
    protected $result;
    protected $message;
    
    public function __construct(CurlHandle $handle, $result, $message)
    {
        $this->handle = $handle;
        $this->result = $result;
        $this->message = $message;
    }
    
    public function getConnection()
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
