<?php
namespace Yjv\HttpQueue\Request;

use Yjv\HttpQueue\Url\Url;

use Yjv\HttpQueue\Curl\CurlHandle;

use Symfony\Component\HttpFoundation\HeaderBag;

class Request implements RequestInterface
{
    protected $curlOptions;
    protected $url;
    protected $method;
    protected $headers;
    protected $body;
    protected $requestMediator;
    
    public function __construct($url, $method = RequestInterface::METHOD_GET, $headers = array(), $body = '')
    {
        $this->curlOptions = array(
                CURLOPT_CONNECTTIMEOUT => 150,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_HEADER         => false,
                // Verifies the authenticity of the peer's certificate
                CURLOPT_SSL_VERIFYPEER => 1,
                // Certificate must indicate that the server is the server to which you meant to connect
                CURLOPT_SSL_VERIFYHOST => 2
        );
        $this->setUrl($url);
        $this->setMethod($method);
        $this->setHeaders($headers);
    }
    
    public function createHandle()
    {
        $handle = new CurlHandle();
        $curlOptions = $this->curlOptions;
        $method = $this->method;
        $curlOptions[CURLOPT_HTTPHEADER] = $this->headers->allPreserveCase();
        
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
        
//         // Enable the progress function if the 'progress' param was set
//         if ($requestCurlOptions->get('progress')) {
//             // Wrap the function in a function that provides the curl handle to the mediator's progress function
//             // Using this rather than injecting the handle into the mediator prevents a circular reference
//             $curlOptions[CURLOPT_PROGRESSFUNCTION] = function () use ($mediator, $handle) {
//                 $args = func_get_args();
//                 $args[] = $handle;
//                 call_user_func_array(array($mediator, 'progress'), $args);
//             };
//             $curlOptions[CURLOPT_NOPROGRESS] = false;
//         }
        $curlOptions[CURLOPT_COOKIE] = $this->headers->getCookies(RequestHeaderBag::COOKIES_STRING);
        
        $handle->setOptions($curlOptions);
        
        return $handle;
    }
    
    public function setCurlOption($name, $value)
    {
        $this->curlOptions[$name] = $value;
        return $this;
    }
    
    public function setUrl($url)
    {
        if (is_string($url)) {
            
            $url = Url::createFromString($url);
        }
        
        $this->url = $url;
        $this->setCurlOption(CURLOPT_URL, (string)$url);
        return $this;
    }
    
    public function getUrl()
    {
        return $this->url;
    }
    
    public function setMethod($method)
    {
        $this->method = $method;
        
        if ($method == RequestInterface::METHOD_GET) {
            
            return $this->setCurlOption(CURLOPT_HTTPGET, true);
        }
        
        return $this->setCurlOption(CURLOPT_CUSTOMREQUEST, $method);
    }
    
    public function getMethod()
    {
        return $this->method;
    }
    
    public function setHeaders($headers)
    {
        $this->headers = $headers instanceof RequestHeaderBag ? $headers : new RequestHeaderBag($headers);
        return $this;
    }
    
    public function getHeaders()
    {
        return $this->headers;
    }
    
    public function setRequestMediator(RequestMediatorInterface $requestMediator)
    {
        $this->requestMediator = $requestMediator;
        return $this;
    }
}
