<?php
namespace Yjv\HttpQueue\Cookie;

use Symfony\Component\HttpFoundation\Cookie;

class Factory
{
    public static function createFromSetCookieHeader($header)
    {
        $metadata = array(
            'expires' => 0,
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => false
        );
        
        if(!preg_match_all('#([a-zA-Z_-]+)\s*(?:=([^;]*))?#', $header, $matches, PREG_SET_ORDER))
        {
            throw new \InvalidArgumentException('The header sent is malformed');
        }

        $nameValue = array_shift($matches);
        
        if (count($nameValue) != 3) {
            
            throw new \InvalidArgumentException('The header sent is malformed');
        }

        list($useless, $name, $value) = $nameValue;
        
        foreach ($matches as $match) {
            $metadata[strtolower($match[1])] = isset($match[3]) ? $match[3] : (isset($match[2]) ? $match[2] : true);
        }
        
        return new Cookie(
            $name, 
            $value,
            $metadata['expires'],
            $metadata['path'],
            $metadata['domain'],
            $metadata['secure'],
            $metadata['httponly']
        );
    }
    
    public static function createMultipleFromCookieHeader($header)
    {
        return array_map(
            function($cookieData)
            {
                list($name, $value) = explode('=', $cookieData, 2);
                return new Cookie(trim($name), trim($value));
            }, 
            array_filter(
                explode(';', $header), 
                function($value)
                {
                    return stripos($value, '=') !== false;
                }
            )
        );
    }
}
