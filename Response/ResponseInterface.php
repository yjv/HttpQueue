<?php
namespace Yjv\HttpQueue\Response;

interface ResponseInterface
{
    /**
     * @return int
     */
    public function getCode();
    
    /**
     * @return string
     */
    public function getStatusMessage();
    
    /**
     * @return \Yjv\HttpQueue\Request\RequestHeaderBag
     */
    public function getHeaders();
    
    /**
     * @return \Yjv\HttpQueue\Transport\Payload\PayloadSourceInterface
     */
    public function getBody();
}