<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Payload\StreamPayload;

class StreamDestinationPayloadFactory implements DestinationPayloadFactoryInterface
{
    public function getDestinationPayload(ConnectionHandleInterface $handle, RequestInterface $request, ResponseInterface $response)
    {
        return new StreamPayload(fopen('php://temp', 'r+'));
    }
}
