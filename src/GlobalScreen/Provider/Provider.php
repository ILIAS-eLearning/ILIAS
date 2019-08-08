<?php namespace ILIAS\GlobalScreen\Provider;

/**
 * Interface Provider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Provider
{

    /**
     * @return string
     */
    public function getFullyQualifiedClassName() : string;


    /**
     * @return string
     */
    public function getProviderNameForPresentation() : string;
}
