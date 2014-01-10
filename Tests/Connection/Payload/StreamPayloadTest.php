<?php
namespace Yjv\HttpQueue\Tests\Connection\Payload;

use Yjv\HttpQueue\Tests\Stream\StreamTest;

use Yjv\HttpQueue\Connection\Payload\StreamPayload;

use Mockery;

class StreamPayloadTest extends StreamTest
{
    public function testGetSize()
    {
        $size = filesize(__DIR__ . '/../../bootstrap.php');
        $handle = fopen(__DIR__ . '/../../bootstrap.php', 'r');
        $stream = new StreamPayload($handle);
        $this->assertEquals($handle, $stream->getStream());
        $this->assertEquals($size, $stream->getSize());
        $this->assertEquals($size, $stream->getSize());
        unset($stream);
    
//if you have node js installed then uncomment these lines
//         // Make sure that false is returned when the size cannot be determined
//         $this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n");
//         $handle = fopen('http://localhost:' . $this->getServer()->getPort(), 'r');
//         $stream = new Stream($handle);
//         $this->assertEquals(false, $stream->getSize());
//         unset($stream);
    }
    
    public function testStreamWriting()
    {
        $payload = new StreamPayload(fopen('php://temp', 'c+'));
        $payload->writeStream('cxcxzet');
        $data = 'ddasdadasasd';
        $payload->setSourceHandle(Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface'));
        $payload->writeStream($data);
        $this->assertEquals($data, (string)$payload);
    }
    
    public function testContentGetters()
    {
        $payload = new StreamPayload(fopen('php://temp', 'c+'));
        $this->assertNull($payload->getContentType());
        $this->assertNull($payload->getContentLength());
    }
    
    public function testReadStream()
    {
        $stream = fopen('php://temp', 'c+');
        $payload = new StreamPayload($stream);
        fwrite($stream, 'part1part22part333');
        $payload->setDestinationHandle(Mockery::mock('Yjv\HttpQueue\Connection\ConnectionHandleInterface'));
        $this->assertEquals('part1', $payload->readStream(5));
        $this->assertEquals('part22', $payload->readStream(6));
    }
}
