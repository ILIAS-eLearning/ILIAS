<?php namespace ILIAS\GlobalScreen\Provider;

/**
 * Interface ProviderFactoryInterface
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ProviderFactoryInterface
{

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
}
