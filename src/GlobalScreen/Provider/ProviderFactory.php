<?php namespace ILIAS\GlobalScreen\Provider;

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
    public function getProviderByClassName(string $class_name) : Provider
    {
        global $DIC;

        return new $class_name($DIC);
    }


    /**
     * @inheritDoc
     */
    public function isInstanceCreationPossible(string $class_name) : bool
    {
        return class_exists($class_name);
    }
}
