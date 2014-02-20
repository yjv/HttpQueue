<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Transport\HandleInterface;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Transport\Payload\StreamPayloadHolder;

class TempDestinationStreamFactory implements DestinationPayloadFactoryInterface
{
    public function getDestinationPayload(HandleInterface $handle, RequestInterface $request, ResponseInterface $response)
    {
        return new StreamPayloadHolder(fopen('php://temp', 'r+'));
    }
}
