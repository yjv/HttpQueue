<?php
namespace Yjv\HttpQueue\Curl;

class CurlEvents
{
    const SEND_BODY = 'curl.send_body';
    const RECEIVE_BODY = 'curl.receive_body';
    const RECEIVE_HEADER = 'curl.receive_header';
    const PROGRESS = 'curl.progress';
    const PASSWORD_REQUESTED = 'curl.password_requested';
    
    public static function getCallbackEvents($callback = null)
    {
        $callbacks = array(
            CURLOPT_WRITEFUNCTION => self::RECEIVE_BODY,
            CURLOPT_READFUNCTION => self::SEND_BODY,
            CURLOPT_HEADERFUNCTION => self::RECEIVE_HEADER,
            CURLOPT_PROGRESSFUNCTION => self::PROGRESS
        );
        
        if (defined('CURLOPT_PASSWDFUNCTION')) {
            
            $callbacks[CURLOPT_PASSWDFUNCTION] = self::PASSWORD_REQUESTED;
        }
        
        if (is_null($callback)) {
            
            return $callbacks;
        }
        
        return isset($callbacks[$callback]) ? $callbacks[$callback] : null;
    }
}
