<?php
namespace Yjv\HttpQueue\Tests\Uri;

use Yjv\HttpQueue\Uri\Uri;

use Yjv\HttpQueue\Uri\Query;

use Yjv\HttpQueue\Uri\Path;

use Yjv\HttpQueue\Uri\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateUriFromString()
    {
        $uriString = 'http://usr:pss@example.com:81/mypath/myfile.html?a=b&b[]=2&b[]=3#myfragment';
        $expectedUri = new Uri();
        $expectedUri
            ->setScheme('http')
            ->setUsername('usr')
            ->setPassword('pss')
            ->setHost('example.com')
            ->setPort('81')
            ->setPath(Factory::createPathFromString('/mypath/myfile.html'))
            ->setQuery(Factory::createQueryFromString('a=b&b[]=2&b[]=3'))
            ->setFragment('myfragment')
        ;
        $uri = Factory::createUriFromString($uriString);
        $this->assertEquals($expectedUri, $uri);
    }
    
    public function testCreatePathFromString()
    {
        $pathString = '/asd%26%2Fasd/adsdasdsa.ertrte';
        $this->assertEquals(new Path(array('asd&/asd', 'adsdasdsa'), 'ertrte'), Factory::createPathFromString($pathString));
    }
    
    public function testCreateQueryFromString()
    {
        $queryString = 'a=b&b[]=2&b[]=3&asd[xcvcvx]=cbvbcv';
        $this->assertEquals(new Query(array(
                'a' => 'b',
                'b' => array('2', '3'),
                'asd' => array('xcvcvx' => 'cbvbcv')
        )), Factory::createQueryFromString($queryString));
    }    
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage there was an error parsing the url string.
     */
    public function testStringConversionWithBadUri()
    {
        var_dump(Factory::createUriFromString('http://dsaads:ewqweq@asdsad:585758756865'));
    }
}
