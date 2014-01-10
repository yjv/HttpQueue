<?php
namespace Yjv\HttpQueue\Tests\Queue;

use Yjv\HttpQueue\Curl\CurlHandle;

use Yjv\HttpQueue\Request\RequestInterface;

use Yjv\HttpQueue\Request\Request;

use Yjv\HttpQueue\Queue\CurlHandleFactory;

class CurlHandleFactoryTest extends \PHPUnit_Framework_TestCase
{
    protected $factory;
    
    public function setUp()
    {
        $this->factory = new CurlHandleFactory();
    }
    
    public function testCreateHandle()
    {
        list($url, $method, $headers, $handleOptions) = $this->getBaseData();
        
        $request = new Request(
            $url, 
            $method, 
            $headers
        );
        $request->setHandleOption(CURLOPT_AUTOREFERER, true);
        $request->setHandleOption(CURLOPT_RETURNTRANSFER, true);
        $handleOptions[CURLOPT_AUTOREFERER] = true;
        $handleOptions[CURLOPT_RETURNTRANSFER] = true;
        $returnedHandle = $this->factory->createHandle($request);
        $this->assertEquals($handleOptions, $returnedHandle->getOptions());
    }
    
    public function testCreateHandleWhereUrlHasAPort()
    {
        list($url, $method, $headers, $handleOptions) = $this->getBaseData();
        $request = new Request(
            'http://asdsa.asddas.com:80/adsdsa', 
            $method, 
            $headers
        );
        $handleOptions[CURLOPT_PORT] = 80;
        $handleOptions[CURLOPT_URL] = 'http://asdsa.asddas.com:80/adsdsa';
        
        $returnedHandle = $this->factory->createHandle($request);
        $this->assertEquals($handleOptions, $returnedHandle->getOptions());
    }
    
    public function testCreateHandleWhereMethodIsNotGet()
    {
        list($url, $method, $headers, $handleOptions) = $this->getBaseData();
        $request = new Request(
            $url, 
            RequestInterface::METHOD_POST, 
            $headers
        );
        unset($handleOptions[CURLOPT_HTTPGET]);
        $handleOptions[CURLOPT_CUSTOMREQUEST] = RequestInterface::METHOD_POST;
        
        $returnedHandle = $this->factory->createHandle($request);
        $this->assertEquals($handleOptions, $returnedHandle->getOptions());
    }
    
    public function testCreateHandleWhereMethodIsHead()
    {
        list($url, $method, $headers, $handleOptions) = $this->getBaseData();
        $request = new Request(
            $url, 
            RequestInterface::METHOD_HEAD, 
            $headers
        );
        unset($handleOptions[CURLOPT_HTTPGET]);
        $handleOptions[CURLOPT_CUSTOMREQUEST] = RequestInterface::METHOD_HEAD;
        $handleOptions[CURLOPT_NOBODY] = true;
        
        $returnedHandle = $this->factory->createHandle($request);
        $this->assertEquals($handleOptions, $returnedHandle->getOptions());
    }
    
    public function testCreateHandleWhereAcceptEncodingHeaderPresent()
    {
        list($url, $method, $headers, $handleOptions) = $this->getBaseData();
        $request = new Request(
            $url, 
            $method, 
            $headers
        );
        $request->getHeaders()->set('Accept-Encoding', array('encoding'));
        
        array_pop($handleOptions[CURLOPT_HTTPHEADER]);
        $handleOptions[CURLOPT_HTTPHEADER][] = 'Accept: ';
        $handleOptions[CURLOPT_HTTPHEADER][] = 'Expect: ';
        
        $handleOptions[CURLOPT_ENCODING] = 'encoding';
        
        $returnedHandle = $this->factory->createHandle($request);
        $this->assertEquals($handleOptions, $returnedHandle->getOptions());
    }
    
    public function testCreateHandleWhereCookieHeaderPresent()
    {
        list($url, $method, $headers, $handleOptions) = $this->getBaseData();
    
        $request = new Request(
                $url,
                $method,
                $headers
        );
        $request->getHeaders()->set('Cookie', 'sfd=sfd; sfd=wer');
        $handleOptions[CURLOPT_COOKIE] = 'sfd=sfd; sfd=wer';
        $returnedHandle = $this->factory->createHandle($request);
        $this->assertEquals($handleOptions, $returnedHandle->getOptions());
    }
    
    public function testCreateHandleWhereTrackProgressIsTru()
    {
        list($url, $method, $headers, $handleOptions) = $this->getBaseData();
    
        $request = new Request(
                $url,
                $method,
                $headers
        );
        $request->setOption(CurlHandleFactory::TRACK_PROGRESS_OPTION, true);
        $handleOptions[CURLOPT_NOPROGRESS] = false;
        $handleOptions[CURLOPT_PROGRESSFUNCTION] = function(){};
        $returnedHandle = $this->factory->createHandle($request);
        $this->assertEquals($handleOptions, $returnedHandle->getOptions());
    }
    
    protected function getBaseData()
    {
        $url = 'http://asdsa.asddas.com/adsdsa';
        
        return array(
            $url,
            RequestInterface::METHOD_GET,
            array('key' => array('value1', 'value2'), 'key2' => array('value3')),
            array(
                CURLOPT_CONNECTTIMEOUT => 150,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_HEADER         => false,
                // Verifies the authenticity of the peer's certificate
                CURLOPT_SSL_VERIFYPEER => 1,
                // Certificate must indicate that the server is the server to which you meant to connect
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_URL => $url,
                CURLOPT_HTTPGET => true,
                CURLOPT_HTTPHEADER => array('key: value1', 'key: value2', 'key2: value3', 'Expect: '),
                
            )
        );
    }
}
