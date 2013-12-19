<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Response\ResponseInterface;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface DestinationStreamFactoryInterface
{
    public function getDestinationStream(ConnectionHandleInterface $handle, RequestInterface $request, ResponseInterface $response);
}
