<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Connection\Payload\StreamPayload;

class TempDestinationStreamFactory implements DestinationPayloadFactoryInterface
{
    public function getDestinationPayload(ConnectionHandleInterface $handle, RequestInterface $request, ResponseInterface $response)
    {
        return new StreamPayload(fopen('php://temp', 'r+'));
    }
}
