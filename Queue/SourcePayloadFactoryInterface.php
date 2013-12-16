<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

interface SourcePayloadFactoryInterface
{
    public function getSourcePayload(ConnectionHandleInterface $handle, RequestInterface $request);
}
