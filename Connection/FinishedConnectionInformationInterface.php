<?php
namespace Yjv\HttpQueue\Connection;

interface FinishedConnectionInformationInterface
{
    public function getConnection();
    public function getResult();
    public function getMessage();
}
