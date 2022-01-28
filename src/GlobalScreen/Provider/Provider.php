<?php namespace ILIAS\GlobalScreen\Provider;

/**
 * Interface Provider
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Provider
{
    public function getFullyQualifiedClassName() : string;
    
    public function getProviderNameForPresentation() : string;
}
