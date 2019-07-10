<?php namespace ILIAS\GlobalScreen\Provider;

use ReflectionException;

/**
 * Interface ProviderFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ProviderFactoryInterface
{

    /**
     * @param string $class_name
     * @param array  $args
     *
     * @return Provider
     *
     * @throws ReflectionException
     */
    public function getProviderByClassName(string $class_name, array $args = []) : Provider;


    /**
     * @param string $class_name
     *
     * @return bool
     */
    public function isInstanceCreationPossible(string $class_name) : bool;
}
