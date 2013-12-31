<?php
namespace Yjv\HttpQueue\Uri;

class Factory
{
    public static function createUriFromString($uriString)
    {
        $uriParts = parse_url($uriString);
    
        if ($uriParts === false) {
    
            throw new \InvalidArgumentException('there was an error parsing the url string.');
        }
    
        $uriParts = array_merge(array(
                'scheme' => '',
                'port' => '',
                'user' => '',
                'pass' => '',
                'host' => '',
                'path' => '',
                'query' => '',
                'fragment' => '',
        ), $uriParts);
    
        $uri = new Uri();
        $uri
            ->setScheme($uriParts['scheme'] ?: 'http')
            ->setPort($uriParts['port'])
            ->setUsername($uriParts['user'])
            ->setPassword($uriParts['pass'])
            ->setHost($uriParts['host'])
            ->setPath(static::createPathFromString($uriParts['path']))
            ->setQuery(static::createQueryFromString($uriParts['query']))
            ->setFragment($uriParts['fragment'])
        ;
        return $uri;
    }    
    
    public static function createPathFromString($pathString)
    {
        $pathArray = explode('/', $pathString);
        
        if ($pathArray[0] == '') {
            
            array_shift($pathArray);
        }
        
        $extension = '';
        $lastPiece = array_pop($pathArray);
        
        if ($lastPiece !== false) {
        
            if (($extensionPosition = stripos($lastPiece, '.')) !== false) {
        
                $extension = substr($lastPiece, $extensionPosition + 1);
                $lastPiece = substr($lastPiece, 0, $extensionPosition);
            }
        
            $pathArray[] = $lastPiece;
        }
        
        return new Path(array_map(function($value)
        {
            return rawurldecode($value);
        }, $pathArray), $extension);
    }    
    
    public static function createQueryFromString($queryString)
    {
        parse_str($queryString, $queryParams);
        return new Query($queryParams);
    }
}
