<?php
namespace Yjv\HttpRequest\Request;

interface RequestInterface
{
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_HEAD = 'HEAD';
    
    /**
     * @return a curl handle this method should idempotent and return a new handle each time it's called
     */
    public function getHandle();
}
