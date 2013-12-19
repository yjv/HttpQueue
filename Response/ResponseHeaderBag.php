<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yjv\HttpQueue\Response;

use Symfony\Component\HttpFoundation\HeaderBag;

use Symfony\Component\HttpFoundation\Cookie;

use Symfony\Component\HttpFoundation\ResponseHeaderBag as BaseResponseHeaderBag;

/**
 * ResponseHeaderBag is a container for Response HTTP headers.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @api
 */
class ResponseHeaderBag extends BaseResponseHeaderBag
{
    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        ksort($this->headerNames);

        return HeaderBag::__toString();
    }
    
    public function replace(array $headers = array())
    {
        $this->cookies = array();
        parent::replace($headers);
    }
    
    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function set($key, $values, $replace = true)
    {
        parent::set($key, $values, $replace);

        $uniqueKey = strtr(strtolower($key), '_', '-');
        
        if ($uniqueKey == 'set-cookie') {
            
            if ($replace) {
                
                $this->cookies = array();
            }
            
            foreach ((array)$values as $header) {
                
                $this->setCookie($this->parseSetCookie($header));
            }
        }
    }
    
    protected function parseSetCookie($header)
    {
        list($name, $value) = explode('=', $header, 2);
        $values = explode(';', $value);
        
        $value = array_shift($values);
        
        $metadata = array(
            'expires' => 0,
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => false
        );
        
        foreach ($values as $metadataString) {
            
            $parsedMetadataString = explode('=', $metadataString, 2);
            $parsedMetadataString[0] = strtolower(trim($parsedMetadataString[0]));
            
            if (!isset($parsedMetadataString[1])) {
                
                $parsedMetadataString[1] = true;
            } else {
                
                $parsedMetadataString[1] = trim($parsedMetadataString[1]);
            }
            
            $metadata[$parsedMetadataString[0]] = $parsedMetadataString[1];
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
}
