<?php
namespace Yjv\HttpQueue\Stream;

use Guzzle\Stream\Stream as BaseStream;

class Stream extends BaseStream
{
    public function truncate($size)
    {
        if ($this->isSeekable() && $this->isWritable()) {
    
            return ftruncate($this->stream, $size);
        }
    
        return false;
    }
}
