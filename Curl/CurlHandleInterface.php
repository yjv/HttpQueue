<?php
namespace Yjv\HttpQueue\Curl;

interface CurlHandleInterface extends CurlResourceInterface
{
    public function setOptions(array $options);
    public function setOption($name, $value);
    public function getOptions();
    public function execute();
    public function getLastTransferInfo($option = 0);
}
