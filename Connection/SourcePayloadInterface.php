<?php
namespace Yjv\HttpQueue\Connection;

interface SourcePayloadInterface extends PayloadInterface
{
    public function getPayloadContent();
}
