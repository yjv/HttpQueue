<?php
namespace Yjv\HttpQueue\Uri;

class Path extends \ArrayObject
{
    protected $extension;

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
