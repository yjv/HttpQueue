<?php
namespace Yjv\HttpQueue\Transport\Payload;

use Yjv\HttpQueue\Transport\HandleInterface;

interface PayloadHolderInterface
{
    public function __toString();
}
