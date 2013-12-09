<?php
namespace Yjv\HttpQueue\Curl;

interface CurlResourceInterface
{
    public function getResource();
    public function close();
}
