<?php namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\ItemInformation;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\StaticMainMenuProvider;

/**
 * Interface ProviderFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ProviderFactoryInterface
{

    /**
     * @return StaticMainMenuProvider[]
     */
    public function getMainBarProvider() : array;


    /**
     * @return ItemInformation
     */
    public function getMainBarItemInformation() : ItemInformation;


    /**
     * @param string $class_name
     *
     * @return Provider
     */
    public function getProviderByClassName(string $class_name) : Provider;


    /**
     * @param string $class_name
     *
     * @return bool
     */
    public function isInstanceCreationPossible(string $class_name) : bool;


    /**
     * @param string $class_name
     *
     * @return bool
     */
    public function isRegistered(string $class_name) : bool;
}
