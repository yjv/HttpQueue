<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Payload\StreamPayload;

class StreamDestinationPayloadFactory implements DestinationPayloadFactoryInterface
{
    public function getDestinationPayload(ConnectionHandleInterface $handle, RequestInterface $request)
    {
        return new StreamPayload(fopen('php://temp', 'r+'));
    }
}
