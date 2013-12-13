<?php
namespace Yjv\HttpQueue\Queue;

use Yjv\HttpQueue\Curl\CurlHandle;

use Yjv\HttpQueue\Request\RequestInterface;

class CurlHandleConnectionFactory implements ConnectionFactoryInterface
{
    public function createConnection(RequestInterface $request)
    {
        $handle = new CurlHandle();
        $curlOptions = $request->getCurlOptions();
        $method = $request->getMethod();
        $curlOptions[CURLOPT_HTTPHEADER] = $request->getHeaders()->allPreserveCase();
        
        if (defined('CURLOPT_PROTOCOLS')) {
            // Allow only HTTP and HTTPS protocols
            $curlOptions[CURLOPT_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
        }
        
        // Add CURLOPT_ENCODING if Accept-Encoding header is provided
        //         if ($acceptEncodingHeader = $request->getHeader('Accept-Encoding')) {
        //             $curlOptions[CURLOPT_ENCODING] = (string) $acceptEncodingHeader;
        //             // Let cURL set the Accept-Encoding header, prevents duplicate values
        //             $request->removeHeader('Accept-Encoding');
        //         }
        
        //         // Specify settings according to the HTTP method
        //         if ($method == 'GET') {
        //             $curlOptions[CURLOPT_HTTPGET] = true;
        //         } elseif ($method == 'HEAD') {
        //             $curlOptions[CURLOPT_NOBODY] = true;
        //             // HEAD requests do not use a write function
        //             unset($curlOptions[CURLOPT_WRITEFUNCTION]);
        //         } elseif (!($request instanceof EntityEnclosingRequest)) {
        //             $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        //         } else {
        
        //             $curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
        
        //             // Handle sending raw bodies in a request
        //             if ($request->getBody()) {
        //                 // You can send the body as a string using curl's CURLOPT_POSTFIELDS
        //                 if ($bodyAsString) {
        //                     $curlOptions[CURLOPT_POSTFIELDS] = (string) $request->getBody();
        //                     // Allow curl to add the Content-Length for us to account for the times when
        //                     // POST redirects are followed by GET requests
        //                     if ($tempContentLength = $request->getHeader('Content-Length')) {
        //                         $tempContentLength = (int) (string) $tempContentLength;
        //                     }
        //                     // Remove the curl generated Content-Type header if none was set manually
        //                     if (!$request->hasHeader('Content-Type')) {
        //                         $curlOptions[CURLOPT_HTTPHEADER][] = 'Content-Type:';
        //                     }
        //                 } else {
        //                     $curlOptions[CURLOPT_UPLOAD] = true;
        //                     // Let cURL handle setting the Content-Length header
        //                     if ($tempContentLength = $request->getHeader('Content-Length')) {
        //                         $tempContentLength = (int) (string) $tempContentLength;
        //                         $curlOptions[CURLOPT_INFILESIZE] = $tempContentLength;
        //                     }
        //                     // Add a callback for curl to read data to send with the request only if a body was specified
        //                     $curlOptions[CURLOPT_READFUNCTION] = array($mediator, 'readRequestBody');
        //                     // Attempt to seek to the start of the stream
        //                     $request->getBody()->seek(0);
        //                 }
        
        //             } else {
        
        //                 // Special handling for POST specific fields and files
        //                 $postFields = false;
        //                 if (count($request->getPostFiles())) {
        //                     $postFields = $request->getPostFields()->useUrlEncoding(false)->urlEncode();
        //                     foreach ($request->getPostFiles() as $key => $data) {
        //                         $prefixKeys = count($data) > 1;
        //                         foreach ($data as $index => $file) {
        //                             // Allow multiple files in the same key
        //                             $fieldKey = $prefixKeys ? "{$key}[{$index}]" : $key;
        //                             $postFields[$fieldKey] = $file->getCurlValue();
        //                         }
        //                     }
        //                 } elseif (count($request->getPostFields())) {
        //                     $postFields = (string) $request->getPostFields()->useUrlEncoding(true);
        //                 }
        
        //                 if ($postFields !== false) {
        //                     if ($method == 'POST') {
        //                         unset($curlOptions[CURLOPT_CUSTOMREQUEST]);
        //                         $curlOptions[CURLOPT_POST] = true;
        //                     }
        //                     $curlOptions[CURLOPT_POSTFIELDS] = $postFields;
        //                     $request->removeHeader('Content-Length');
        //                 }
        //             }
        
        //             // If the Expect header is not present, prevent curl from adding it
        //             if (!$this->headers->has('Expect')) {
        //                 $curlOptions[CURLOPT_HTTPHEADER][] = 'Expect:';
        //             }
        //         }
        
        //         // If a Content-Length header was specified but we want to allow curl to set one for us
        //         if (null !== $tempContentLength) {
        //             $request->removeHeader('Content-Length');
        //         }
        
        // Do not set an Accept header by default
        if (!isset($curlOptions[CURLOPT_ENCODING])) {
            $curlOptions[CURLOPT_HTTPHEADER][] = 'Accept:';
        }
        
        //         // Add the content-length header back if it was temporarily removed
        //         if ($tempContentLength) {
        //             $request->setHeader('Content-Length', $tempContentLength);
        //         }
        
        // Enable the progress function if the 'progress' param was set
        if (!empty($curlOptions[CURLOPT_NOPROGRESS])) {
        
            // Wrap the function in a function that provides the curl handle to the mediator's progress function
            // Using this rather than injecting the handle into the mediator prevents a circular reference
            $curlOptions[CURLOPT_PROGRESSFUNCTION] = array($this->requestMediator, 'progress');
        }
        
        $curlOptions[CURLOPT_COOKIE] = $this->headers->getCookies(RequestHeaderBag::COOKIES_STRING);
        
        $handle->setOptions($curlOptions);
        
        return $handle;
    }
}
