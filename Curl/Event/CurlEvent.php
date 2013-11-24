<?php
namespace Yjv\HttpQueue\Curl;

use Yjv\HttpRequest\Queue\CurlMultiInterface;

class CurlEvent
{
    protected $curlMulti;
    protected $handle;
    
    public function __construct(CurlMultiInterface $curlMulti, $handle)
    {
        $this->curlMulti = $curlMulti;
        $this->handle = $handle;
    }
    
    public function getCurlMulti()
    {
        return $this->curlMulti;
    }
    
    public function getHandle()
    {
        return $this->handle;
    }
}
