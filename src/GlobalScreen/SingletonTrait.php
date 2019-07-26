<?php namespace ILIAS\GlobalScreen;

/**
 * Class SingletonTrait
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait SingletonTrait
{

    /**
     * @var array
     */
    private static $services = [];


    /**
     * @param string $class_name
     *
     * @return mixed
     */
    private function get(string $class_name)
    {
        if (!$this->has($class_name)) {
            self::$services[$class_name] = new $class_name();
        }

        return self::$services[$class_name];
    }


    /**
     * @param string $class_name
     *
     * @return mixed
     */
    private function getWithArgument(string $class_name, $argument)
    {
        if (!$this->has($class_name)) {
            self::$services[$class_name] = new $class_name($argument);
        }

        return self::$services[$class_name];
    }


    /**
     * @param string $class_name
     * @param array  $arguments
     *
     * @return mixed
     * @throws \ReflectionException
     */
    private function getWithMultipleArguments(string $class_name, array $arguments)
    {
        if (!$this->has($class_name)) {
            $i = new \ReflectionClass($class_name);

            self::$services[$class_name] = $i->newInstanceArgs($arguments);
        }

        return self::$services[$class_name];
    }


    /**
     * @param string $class_name
     *
     * @return bool
     */
    private function has(string $class_name) : bool
    {
        return isset(self::$services[$class_name]);
    }
}
