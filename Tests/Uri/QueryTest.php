<?php
namespace Yjv\HttpQueue\Tests\Uri;

use Yjv\HttpQueue\Uri\Query;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    protected $query;
    
    public function setUp()
    {
        $this->query = new Query();
    }
    
    public function testGettersSetters()
    {
        $query = new Query(array(
            'a' => 'b', 
            'b' => array('2', '3'), 
            'asd' => array('xcvcvx' => 'cbvbcv')
        ));
        $this->assertEquals(array(
            'a=b', 
            'b[]=2', 
            'b[]=3', 
            'asd[xcvcvx]=cbvbcv'
        ), $query->getParameterizedArray());
        $this->assertFalse($query->getLiteralIntegerIndexes());
        $this->assertSame($query, $query->setLiteralIntegerIndexes(true));
        $this->assertTrue($query->getLiteralIntegerIndexes());
        $this->assertEquals(array(
            'a=b', 
            'b[0]=2', 
            'b[1]=3', 
            'asd[xcvcvx]=cbvbcv'
        ), $query->getParameterizedArray(true));
    }
    
    public function testStringConversion()
    {
        $queryString = 'a=b&b[]=2&b[]=3&asd[xcvcvx]=cbvbcv';
        $queryStringWithLiteralIndexes = 'a=b&b[0]=2&b[1]=3&asd[xcvcvx]=cbvbcv';
        $query = new Query(array(
            'a' => 'b', 
            'b' => array('2', '3'), 
            'asd' => array('xcvcvx' => 'cbvbcv')
        ));
        $this->assertEquals($queryString, (string)$query);
        $this->assertEquals($queryStringWithLiteralIndexes, (string)$query->getString(true));
    }
}
