<?php
namespace Yjv\HttpQueue\Connection;

interface FinishedHandleInformationInterface
{
    public function getHandle();
    public function getResult();
    public function getMessage();
}
