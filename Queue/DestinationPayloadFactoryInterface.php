<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Transport\HandleInterface;

interface DestinationPayloadFactoryInterface
{
    public function getDestinationPayload(HandleInterface $handle, RequestInterface $request, ResponseInterface $response);
}
