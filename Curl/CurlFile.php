<?php
namespace Yjv\HttpQueue\Curl;

use Symfony\Component\HttpFoundation\File\File;

class CurlFile extends File implements CurlFileInterface
{
    protected $mimeType;
    protected $uploadName;
    
    public function getMimeType()
    {
        if (!$this->mimeType) {
            
            $this->mimeType = parent::getMimeType();
        }
        
        return $this->mimeType;
    }
    
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
        return $this;
    }
    
    public function getUploadName()
    {
        if (!$this->uploadName) {
            
            $extension = $this->getExtension();
            $this->uploadName = $this->getBasename($extension ? '.'.$extension : '');
        }
        
        return $this->uploadName;
    }
    
    public function setUploadName($uploadName)
    {
        $this->uploadName = $uploadName;
        return $this;
    }
    
    public function getCurlValue()
    {
        // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
        // See: https://wiki.php.net/rfc/curl-file-upload
        if (class_exists('CURLFile')) {
            
            return new \CURLFile(
                $this->getRealPath(), 
                $this->getMimeType(), 
                $this->getUploadName()
            );
        }

        // Use the old style if using an older version of PHP
        return sprintf(
            '@%s;filename=%s;type=%s', 
            $this->getRealPath(), 
            $this->getUploadName(), 
            $this->getMimeType()
        );
    }
}
