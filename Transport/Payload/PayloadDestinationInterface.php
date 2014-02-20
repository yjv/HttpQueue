<?php
namespace Yjv\HttpQueue\Transport\Payload;

use Yjv\HttpQueue\Transport\HandleInterface;

interface PayloadDestinationInterface extends PayloadHolderInterface
{
    public function setContentType($contentType);
    public function setContentLength($contentLength);
    public function setSourceHandle(HandleInterface $sourceHandle);
}
