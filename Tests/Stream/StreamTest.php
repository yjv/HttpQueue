<?php
namespace Yjv\HttpQueue\Tests\Stream;

use Yjv\HttpQueue\Stream\Stream;

use Guzzle\Tests\Stream\StreamTest as BaseStreamTest;

class StreamTest extends BaseStreamTest
{
    public function testTruncate()
    {
        $stream = new Stream(fopen('http://www.google.com', 'r'));
        $this->assertFalse($stream->truncate(0));
        $stream = new Stream(fopen('php://temp', 'c+'));
        $this->assertTrue($stream->truncate(0));
    }
}
