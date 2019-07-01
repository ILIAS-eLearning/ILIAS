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
        if (!isset(self::$services[$class_name])) {
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
        if (!isset(self::$services[$class_name])) {
            self::$services[$class_name] = new $class_name($argument);
        }

        return self::$services[$class_name];
    }
}
