<?php
namespace Yjv\HttpQueue\Curl;

class CurlMultiException extends \Exception
{
    protected static $multiErrors = array(
        CURLM_BAD_HANDLE      => array('CURLM_BAD_HANDLE', 'The passed-in handle is not a valid curl multi handle.'),
        CURLM_BAD_EASY_HANDLE => array('CURLM_BAD_EASY_HANDLE', "An easy handle was not good/valid. It could mean that it isn't an easy handle at all, or possibly that the handle already is in used by this or another multi handle."),
        CURLM_OUT_OF_MEMORY   => array('CURLM_OUT_OF_MEMORY', 'You are doomed.'),
        CURLM_INTERNAL_ERROR  => array('CURLM_INTERNAL_ERROR', 'This can only be returned if libcurl bugs. Please report it to us!')
    );
    
    public function __construct($code)
    {
        $message = 'Unexpected cURL error: ' . $code;
        
        if (isset(static::$multiErrors[$code])) {
            
            $message = sprintf(
                "cURL error: %s (%s): cURL message: %s", 
                $code, 
                static::$multiErrors[$code][0], 
                static::$multiErrors[$code][1]
            );
        }
        
        parent::__construct($message, $code);
    }
}
