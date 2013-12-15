<?php
namespace Yjv\HttpQueue\Connection;

interface FinishedHandleInformationInterface
{
    public function getConnection();
    public function getResult();
    public function getMessage();
}
