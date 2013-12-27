<?php
namespace Yjv\HttpQueue\Header;

interface HeaderProcessorInterface
{
    public function processHeader($name, $header, HeaderBag $headerBag);
}
