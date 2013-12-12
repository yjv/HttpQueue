<?php
namespace Yjv\HttpQueue\Queue;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Yjv\HttpQueue\RequestResponseHandleMap;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

interface RequestMediatorInterface
{
    /**
     * 
     * @param CurlHandleInterface $handle
     * @param int $totalDownloadSize
     * @param int $amountDownloaded
     * @param int $totalUploadSize
     * @param int $amountUploaded
     */
    public function progress(CurlHandleInterface $handle, $totalDownloadSize, $amountDownloaded, $totalUploadSize, $amountUploaded);
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @param resource $fileDescriptor
     * @param int $maxDataToRead
     */
    public function readRequestBody(CurlHandleInterface $handle, $fileResource, $maxDataToRead);
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @param string $header
     */
    public function writeResponseHeader(CurlHandleInterface $handle, $header);
    
    /**
     * 
     * @param CurlHandleInterface $handle
     * @param data $data
     */
    public function writeResponseBody(CurlHandleInterface $handle, $data);
    
    public function setHandleMap(RequestResponseHandleMap $handleMap);
    public function setDispatcher(EventDispatcherInterface $dispatcher);
    public function setQueue(QueueInterface $queue);
}
