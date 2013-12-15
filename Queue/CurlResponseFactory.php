<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Response\Response;

use Yjv\HttpQueue\Connection\ConnectionHandleInterface;

class CurlResponseFactory implements ResponseFactoryInterface
{
    protected $handles;
    
    public function __construct()
    {
        $this->handles = new \SplObjectStorage();
    }
    
    public function registerHandle(ConnectionHandleInterface $handle)
    {
        $handle->setOption(CURLOPT_HEADERFUNCTION, array($this, 'writeHeader'));
        return $this;
    }
    
    public function getResponse(ConnectionHandleInterface $handle)
    {
        return isset($this->handles[$handle]) ? $this->handles[$handle] : null;
    }
    
    /**
     * Receive a response header from curl
     *
     * @param resource $curl   Curl handle
     * @param string   $header Received header
     *
     * @return int
     */
    public function writeHeader(CurlHandleInterface $handle, $header)
    {
        static $normalize = array("\r", "\n");
        $length = strlen($header);
        $header = str_replace($normalize, '', $header);
    
        if (!$header) {
    
            return;
        }
        
        if (strpos($header, 'HTTP/') === 0) {
    
            $startLine = explode(' ', $header, 3);
            $code = $startLine[1];
            $status = isset($startLine[2]) ? $startLine[2] : '';
    
            $this->handles[$handle] = new Response($code, array());
    
        } elseif ($pos = strpos($header, ':')) {
            
            //if somehow no request is there return 0
            if (!isset($this->handles[$handle])) {
                
                return 0;
            }
            
            $response = $this->handles[$handle];
            $response->getHeaders()->set(
                    trim(substr($header, 0, $pos)),
                    trim(substr($header, $pos + 1)),
                    false
            );
        }
    
        return $length;
    }
}
