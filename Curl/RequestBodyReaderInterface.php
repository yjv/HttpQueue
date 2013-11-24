<?php
namespace Yjv\HttpRequest\Curl;

interface RequestBodyReaderInterface
{
    /**
     * Read data from the request body and send it to curl
     *
     * @param resource $ch     Curl handle
     * @param resource $fd     File descriptor
     * @param int      $length Amount of data to read
     *
     * @return string
     */
    public function readRequestBody($ch, $fd, $length);
}
