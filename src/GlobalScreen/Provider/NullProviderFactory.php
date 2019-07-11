<?php namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;

/**
 * Class NullProviderFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullProviderFactory implements ProviderFactoryInterface
{

    /**
     * @inheritDoc
     */
    public function getMainBarProvider() : array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getMainBarItemInformation() : ItemInformation
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getProviderByClassName(string $class_name) : Provider
    {
        // return new NullP;
    }


    /**
     * @inheritDoc
     */
    public function isInstanceCreationPossible(string $class_name) : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function isRegistered(string $class_name) : bool
    {
        return false;
    }
}
