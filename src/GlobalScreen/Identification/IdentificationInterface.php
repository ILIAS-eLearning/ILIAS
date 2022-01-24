<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Interface IdentificationInterface
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface IdentificationInterface extends \Serializable
{
    
    public function getClassName() : string;
    
    public function getInternalIdentifier() : string;
    
    public function getProviderNameForPresentation() : string;
}
