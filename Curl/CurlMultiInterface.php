<?php
namespace Yjv\HttpQueue\Curl;

interface CurlMultiInterface extends CurlResourceInterface
{
    const STATUS_OK = CURLM_OK;
    const STATUS_PERFORMING = CURLM_CALL_MULTI_PERFORM;
    
    public function addHandle(CurlHandleInterface $handle);
    public function removeHandle(CurlHandleInterface $handle);
    public function execute(&$stillRunning = 0);
    public function getHandleResponseContent(CurlHandleInterface $handle);
    public function select($selectTimeout = 1.0);
    public function getFinishedHandleInformation(&$handleInformationCount = 0);
}
