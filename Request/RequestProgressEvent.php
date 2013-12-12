<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Queue\QueueInterface;

class RequestProgressEvent extends RequestEvent
{
    protected $totalDownloadSize;
    protected $amountDownloaded;
    protected $totalUploadSize;
    protected $amountUploaded;
   
    public function __construct(
        QueueInterface $queue, 
        RequestInterface $request, 
        $totalDownloadSize, 
        $amountDownloaded, 
        $totalUploadSize, 
        $amountUploaded
    ) {
        parent::__construct($queue, $request);
        $this->totalDownloadSize = $totalDownloadSize;
        $this->amountDownloaded = $amountDownloaded;
        $this->totalUploadSize = $totalUploadSize;
        $this->amountUploaded = $amountUploaded;
    }
    
    public function getTotalDownloadSize()
    {
        return $this->totalDownloadSize;
    }
    
    public function getAmountDownloaded()
    {
        return $this->amountDownloaded;
    }
    
    public function getTotalUploadSize()
    {
        return $this->totalUploadSize;
    }
    
    public function getAmountUploaded()
    {
        return $this->amountUploaded;
    }
}
