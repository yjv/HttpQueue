<?php
namespace Yjv\HttpQueue\Curl;

interface CurlHandleFactoryInterface
{
    /**
     * 
     *  @return a curl handle this method should idempotent and return a new handle each time it's called
     */
    public function createHandle();
}
