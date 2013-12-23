<?php
namespace Yjv\HttpQueue\Tests\Uri;

use Yjv\HttpQueue\Uri\Query;

use Yjv\HttpQueue\Uri\Path;

use Yjv\HttpQueue\Uri\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    protected $uri;
    
    public function setUp()
    {
        $this->uri = new Uri();
    }
    
    public function testGettersSetters()
    {
        $scheme = 'scheme';
        $this->assertSame($this->uri, $this->uri->setScheme($scheme));
        $this->assertEquals($scheme, $this->uri->getScheme());
        $port = '123312';
        $this->assertSame($this->uri, $this->uri->setPort($port));
        $this->assertSame(123312, $this->uri->getPort());
        $username = 'sadsadas';
        $this->assertSame($this->uri, $this->uri->setUsername($username));
        $this->assertEquals($username, $this->uri->getUsername());
        $password = 'xvcxvcxvcx';
        $this->assertSame($this->uri, $this->uri->setPassword($password));
        $this->assertEquals($password, $this->uri->getPassword());
        $host = 'asdasd.com';
        $this->assertSame($this->uri, $this->uri->setHost($host));
        $this->assertEquals($host, $this->uri->getHost());
        $this->assertInstanceOf('Yjv\HttpQueue\Uri\Path', $this->uri->getPath());
        $path = new Path(array());
        $this->assertSame($this->uri, $this->uri->setPath($path));
        $this->assertSame($path, $this->uri->getPath());
        $query = new Query();
        $this->assertInstanceOf('Yjv\HttpQueue\Uri\Query', $this->uri->getQuery());
        $this->assertSame($this->uri, $this->uri->setQuery($query));
        $this->assertSame($query, $this->uri->getQuery());
        $fragment = 'uoipioo';
        $this->assertSame($this->uri, $this->uri->setFragment($fragment));
        $this->assertEquals($fragment, $this->uri->getFragment());
    }
    
    public function testStringConversions()
    {
        $uriString = 'http://usr:pss@example.com:81/mypath/myfile.html?a=b&b[]=2&b[]=3#myfragment';
        $expectedUri = new Uri();
        $expectedUri
            ->setScheme('http')
            ->setUsername('usr')
            ->setPassword('pss')
            ->setHost('example.com')
            ->setPort('81')
            ->setPath(Path::createFromString('/mypath/myfile.html'))
            ->setQuery(Query::createFromString('a=b&b[]=2&b[]=3'))
            ->setFragment('myfragment')
        ;
        $uri = Uri::createFromString($uriString);
        $this->assertEquals($expectedUri, $uri);
        $this->assertEquals($uriString, (string)$uri);
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage there was an error parsing the url string.
     */
    public function testStringConversionWithBadUri()
    {
        var_dump(Uri::createFromString('http://dsaads:ewqweq@asdsad:585758756865'));
    }
}
