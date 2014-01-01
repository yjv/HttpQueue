<?php
namespace Yjv\HttpQueue\Tests\Curl;

class InternalFunctionMocker
{
    protected static $mockedFunctions = array();
    
    public static function mockFunction($objectUnderTest, $functionName, $callable)
    {
        if (!is_callable($callable)) {
            
            throw new InvalidArgumentException('$callable must be callable');
        }
        
        static::generateFunctionMock($objectUnderTest, $functionName);
        static::$mockedFunctions[$functionName] = $callable;
    }
    
    public static function runMockedFunction($name, array &$args)
    {
        if (!isset(static::$mockedFunctions[$name])) {
            
            return call_user_func_array($name, $args);
        }
        
        return call_user_func_array(static::$mockedFunctions[$name], $args);
    }
    
    public static function clearMockedFunctions()
    {
        static::$mockedFunctions = array();
    }
    
    protected static function generateFunctionMock($objectUnderTest, $functionName)
    {
        $reflectionClass = new \ReflectionClass($objectUnderTest);
        
        if (function_exists($reflectionClass->getNamespaceName().'\\'.$functionName)) {
            
            return;
        }
        
        $args = array();
        
        $reflectionFunction = new \ReflectionFunction($functionName);
        
        foreach ($reflectionFunction->getParameters() as $key => $parameter) {
            
            $args[] = sprintf(
                '%s$%s',
                $parameter->isPassedByReference() ? '&' : '',
                $parameter->getName()
            );
        }
        
        $functionDefinition = sprintf(<<<DEFINITION
            namespace %s;
        
            function %s(%s)
            {
                \$args = array_slice(array(%s), 0, func_num_args());
                return \%s::runMockedFunction('%s', \$args);
            }
DEFINITION
            ,
            $reflectionClass->getNamespaceName(),
            $functionName,
            implode(', ', array_map(function($value){return $value.' = null';}, $args)),
            implode(', ', $args),
            get_called_class(),
            $functionName
        );
        eval($functionDefinition);
    }
}
