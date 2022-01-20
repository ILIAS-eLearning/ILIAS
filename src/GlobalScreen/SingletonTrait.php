<?php namespace ILIAS\GlobalScreen;

use ReflectionClass;

/**
 * Class SingletonTrait
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait SingletonTrait
{
    private static array $services = [];
    
    private function get(string $class_name) : object
    {
        if (!$this->has($class_name)) {
            self::$services[$class_name] = new $class_name();
        }
        
        return self::$services[$class_name];
    }
    
    private function getWithArgument(string $class_name, $argument) : object
    {
        if (!$this->has($class_name)) {
            self::$services[$class_name] = new $class_name($argument);
        }
        
        return self::$services[$class_name];
    }
    
    private function getWithMultipleArguments(string $class_name, array $arguments) : object
    {
        if (!$this->has($class_name)) {
            $i = new ReflectionClass($class_name);
            
            self::$services[$class_name] = $i->newInstanceArgs($arguments);
        }
        
        return self::$services[$class_name];
    }
    
    private function has(string $class_name) : bool
    {
        return isset(self::$services[$class_name]);
    }
}
