<?php namespace ILIAS\GlobalScreen\Provider;

use ReflectionClass;

/**
 * Class ProviderFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ProviderFactory implements ProviderFactoryInterface
{

    /**
     * @inheritdoc
     */
    public function getProviderByClassName(string $class_name, array $args = []) : Provider
    {
        global $DIC;

	    array_unshift($args, $DIC);

	    $reflect = new ReflectionClass($class_name);

	    /**
	     * @var Provider $instance
	     */
	    $instance = $reflect->newInstanceArgs($args);

	    return $instance;
    }


    /**
     * @inheritDoc
     */
    public function isInstanceCreationPossible(string $class_name) : bool
    {
        return class_exists($class_name);
    }
}
