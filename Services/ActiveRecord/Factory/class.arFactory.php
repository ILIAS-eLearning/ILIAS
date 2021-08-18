<?php

/**
 * Class arFactory
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arFactory
{

    /**
     * @param       $primary_key
     * @throws arException
     */
    public static function getInstance(string $class_name, $primary_key = 0, array $additional_arguments = array()) : object
    {
        /**
         * @var $obj ActiveRecord
         */
        $ref = new ReflectionClass($class_name);
        if ($ref->isInstantiable()) {
            $obj = $ref->newInstanceArgs(array_merge(array($primary_key), $additional_arguments));
            if (empty($primary_key)) {
                $obj = clone($obj);
            }
        } else {
            throw new arException(arException::PRIVATE_CONTRUCTOR);
        }

        return $obj;
    }
}
