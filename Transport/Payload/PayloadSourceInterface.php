<?php
namespace Yjv\HttpQueue\Transport\Payload;

use Yjv\HttpQueue\Transport\HandleInterface;

interface PayloadSourceInterface extends PayloadHolderInterface
{
    public function getContentType();
    public function getContentLength();
    public function setDestinationHandle(HandleInterface $destinationHandle);
}
