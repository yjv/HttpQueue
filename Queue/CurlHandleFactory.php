<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Request\RequestHeaderBag;

use Yjv\HttpQueue\Transport\PayloadHolderInterface;

use Yjv\HttpQueue\Payload\PayloadSourceInterface;

use Yjv\HttpQueue\Payload\StreamSourceInterface;

use Yjv\HttpQueue\Curl\CurlHandle;

use Yjv\HttpQueue\Request\RequestInterface;

class CurlHandleFactory implements HandleFactoryInterface
{
    const TRACK_PROGRESS_OPTION = 'track_progress';
    
    public function createHandle(RequestInterface $request)
    {
        $headers = clone $request->getHeaders();
        $handle = new CurlHandle();
        $curlOptions = array(
            CURLOPT_CONNECTTIMEOUT => 150,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER         => false,
            // Verifies the authenticity of the peer's certificate
            CURLOPT_SSL_VERIFYPEER => 1,
            // Certificate must indicate that the server is the server to which you meant to connect
            CURLOPT_SSL_VERIFYHOST => 2
        );
        
        if (defined('CURLOPT_PROTOCOLS')) {
            // Allow only HTTP and HTTPS protocols
            $curlOptions[CURLOPT_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
        }
        
        $url = $request->getUrl();
        $curlOptions[CURLOPT_URL] = (string)$url;
        
        if ($url->getPort()) {
        
            $curlOptions[CURLOPT_PORT] = $url->getPort();
        }
        
        if ($request->getOption(self::TRACK_PROGRESS_OPTION, false)) {

            $curlOptions[CURLOPT_NOPROGRESS] = false;
            $curlOptions[CURLOPT_PROGRESSFUNCTION] = function(){};
        }
        
        $method = $request->getMethod();
        
        if ($method == RequestInterface::METHOD_GET) {
            
            $curlOptions[CURLOPT_HTTPGET] = true;
        } else {
            
            $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        }
        
        if ($method == RequestInterface::METHOD_HEAD) {
            
            $curlOptions[CURLOPT_NOBODY] = true;
        }

        if ($request->getBody() instanceof PayloadSourceInterface) {
             
            $payload = $request->getBody();
            $handle->setPayloadSource($payload);
            
            if ($payload->getContentType()) {

                $headers->set('Content-Type', $payload->getContentType());
            }
            
            if ($payload->getContentLength()) {

                $headers->set('Content-Length', $payload->getContentLength());
            }
            
        }

        //Add CURLOPT_ENCODING if Accept-Encoding header is provided
        if ($headers->has('Accept-Encoding')) {
        
            $headers->set('Accept', '');
            $curlOptions[CURLOPT_ENCODING] = (string)$headers->get('Accept-Encoding');
        
            // Let cURL set the Accept-Encoding header, prevents duplicate values
            $headers->remove('Accept-Encoding');
        }
        
        // If the Expect header is not present, prevent curl from adding it
        if (!$headers->has('Expect')) {
            
            $headers->set('Expect', '');
        } 
               
        $curlOptions[CURLOPT_HTTPHEADER] = $headers->allPreserveCaseFlattened(array('cookie'));
        
        if ($headers->has('Cookie')) {
            
            $curlOptions[CURLOPT_COOKIE] = $headers->get('Cookie');
        }
        $handle->setOptions($curlOptions);
        $handle->setOptions($request->getHandleOptions());   
        
        return $handle;
    }
}
