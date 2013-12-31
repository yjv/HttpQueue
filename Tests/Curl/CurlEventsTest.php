<?php
namespace Yjv\HttpQueue\Tests\Curl;

use Yjv\HttpQueue\Curl\CurlEvents;

class CurlEventsTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $callbacks = array(
            CURLOPT_WRITEFUNCTION => CurlEvents::RECEIVE_BODY,
            CURLOPT_READFUNCTION => CurlEvents::SEND_BODY,
            CURLOPT_HEADERFUNCTION => CurlEvents::RECEIVE_HEADER,
            CURLOPT_PROGRESSFUNCTION => CurlEvents::PROGRESS
        );
        
        if (defined('CURLOPT_PASSWDFUNCTION')) {
            
            $callbacks[CURLOPT_PASSWDFUNCTION] = CurlEvents::PASSWORD_REQUESTED;
        }
        
        $this->assertEquals($callbacks, CurlEvents::getCallbackEvents());
        $this->assertEquals(CurlEvents::SEND_BODY, CurlEvents::getCallbackEvents(CURLOPT_READFUNCTION));
    }
}
