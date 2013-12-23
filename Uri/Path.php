<?php
namespace Yjv\HttpQueue\Uri;

class Path extends \ArrayObject
{
    protected $extension;
    
    public static function createFromString($pathString)
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
        
        return new static(array_map(function($value)
        {
            return rawurldecode($value);
        }, $pathArray), $extension);
    }
    
    public function __construct(array $pathArray = array(), $extension = '')
    {
        parent::__construct($pathArray);
        $this->extension = $extension;
    }
    
    public function getExtension()
    {
        return $this->extension;
    }
    
    public function setExtension($extension)
    {
        $this->extension = $extension;
        return $this;
    }
    
    public function __toString()
    {
        $pathArray = array();
        
        foreach ($this as $pathPiece) {
            
            $pathArray[] = rawurlencode($pathPiece);
        }
        
        $path = '/' . implode('/', $pathArray);
        
        if ($this->extension) {
            
            $path .= '.' . $this->extension;
        }
        
        return $path;
    }
}
