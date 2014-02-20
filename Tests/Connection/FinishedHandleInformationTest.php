<?php
namespace Yjv\HttpQueue\Tests\Connection;

use Yjv\HttpQueue\Transport\FinishedHandleInformation;

use Mockery;

class FinishedHandleInformationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $handle = Mockery::mock('Yjv\HttpQueue\Transport\HandleInterface');
        $result = 'result';
        $message = 'message';
        $finishedHandleInformation = new FinishedHandleInformation($handle, $result, $message);
        $this->assertSame($handle, $finishedHandleInformation->getHandle());
        $this->assertEquals($result, $finishedHandleInformation->getResult());
        $this->assertEquals($message, $finishedHandleInformation->getMessage());
    }
}
