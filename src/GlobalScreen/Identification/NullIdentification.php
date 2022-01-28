<?php namespace ILIAS\GlobalScreen\Identification;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class NullIdentification
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullIdentification implements IdentificationInterface
{
    protected ?IdentificationInterface $wrapped_identification = null;
    
    /**
     * NullIdentification constructor.
     * @param IdentificationInterface $wrapped_identification
     */
    public function __construct(IdentificationInterface $wrapped_identification = null)
    {
        $this->wrapped_identification = $wrapped_identification;
    }
    
    /**
     * @inheritDoc
     */
    public function serialize()
    {
        if ($this->wrapped_identification !== null) {
            return $this->wrapped_identification->serialize();
        }
        
        return "";
    }
    
    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        // noting to do
    }
    
    /**
     * @inheritDoc
     */
    public function getClassName() : string
    {
        if ($this->wrapped_identification !== null) {
            return $this->wrapped_identification->getClassName();
        }
        
        return "Null";
    }
    
    /**
     * @inheritDoc
     */
    public function getInternalIdentifier() : string
    {
        if ($this->wrapped_identification !== null) {
            return $this->wrapped_identification->getInternalIdentifier();
        }
        
        return "Null";
    }
    
    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation() : string
    {
        if ($this->wrapped_identification !== null) {
            return $this->wrapped_identification->getProviderNameForPresentation();
        }
        
        return "Null";
    }
}
