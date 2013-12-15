<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Connection\PayloadInterface;

use Yjv\HttpQueue\Payload\SourcePayloadInterface;

use Yjv\HttpQueue\Payload\SourceStreamInterface;

use Yjv\HttpQueue\Curl\CurlHandle;

use Yjv\HttpQueue\Request\RequestInterface;

class CurlHandleFactory implements HandleFactoryInterface
{
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
        
        $curlOptions[CURLOPT_NOPROGRESS] = !$request->getTrackProgress();
        
        $method = $request->getMethod();
        
        //Add CURLOPT_ENCODING if Accept-Encoding header is provided
        if ($headers->has('Accept-Encoding')) {
            
            $Headers->set('Accept', '');
            $curlOptions[CURLOPT_ENCODING] = (string)$headers->get('Accept-Encoding');

            // Let cURL set the Accept-Encoding header, prevents duplicate values
            $headers->remove('Accept-Encoding');
        }
        
        if ($method == RequestInterface::METHOD_GET) {
            
            $curlOptions[CURLOPT_HTTPGET] = true;
        } else {
            
            $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        }
        
        if ($method == RequestInterface::METHOD_HEAD) {
            
            $curlOptions[CURLOPT_NOBODY] = true;
        }

        if ($request->getBody() instanceof PayloadInterface) {
             
            if ($request->getBody()->getContentType()) {

                $headers->set('Content-Type', $request->getBody()->getContentType());
            }
            
            if($request->getBody() instanceof SourceStreamInterface) {
            
                $handle->setSourceStream($request->getBody());
                
                if ($headers->has('Content-Length')) {
                
                    $curlOptions[CURLOPT_INFILESIZE] = (int)(string)$headers->get('Content-Length');
                }
            }
            
            if ($request->getBody() instanceof SourcePayloadInterface) {
            
                $handle->setSourcePayload($request->getBody());
                
                if (!$headers->has('Content-Type')) {
                
                    $headers->set('Content-type', '');
                }
            }
            
            $headers->remove('Content-Length');
        }
        
        // If the Expect header is not present, prevent curl from adding it
        if (!$headers->has('Expect')) {
            
            $headers->set('Expect', '');
        } 
               
        $curlOptions[CURLOPT_HTTPHEADER] = $headers->allPreserveCase();
        $curlOptions[CURLOPT_COOKIE] = $headers->getCookies(RequestHeaderBag::COOKIES_STRING);
        $handle->setOptions($curlOptions);
        $curlOptions->setOptions($request->getCurlOptions());        
        
        return $handle;
    }
}
