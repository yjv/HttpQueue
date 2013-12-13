<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Curl\CurlHandleInterface;

use Yjv\HttpQueue\Payload\SourcePayloadInterface;

class FormFieldsPayload implements SourcePayloadInterface
{
    protected $sourceHandle;

    public function attachDestinationHandle(CurlHandleInterface $handle)
    {
        $this->sourceHandle = $handle;
    }
}
