<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface DestinationPayloadFactoryInterface
{
    public function getDestinationPayload(ConnectionHandleInterface $handle, RequestInterface $request);
}
