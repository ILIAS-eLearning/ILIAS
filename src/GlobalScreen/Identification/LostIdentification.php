<?php namespace ILIAS\GlobalScreen\Identification;

/******************************************************************************
 * This file is part of ILIAS, a powerful learning management system.
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *****************************************************************************/

/**
 * Class LostIdentification
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LostIdentification implements IdentificationInterface
{
    private string $serialized_string;
    
    /**
     * NullIdentification constructor.
     * @param IdentificationInterface $wrapped_identification
     */
    public function __construct(string $serialized_string = null)
    {
        $this->serialized_string = $serialized_string;
    }
    
    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return $this->serialized_string;
    }
    
    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
    }
    
    /**
     * @inheritDoc
     */
    public function getClassName() : string
    {
        return "Lost";
    }
    
    /**
     * @inheritDoc
     */
    public function getInternalIdentifier() : string
    {
        return "Lost";
    }
    
    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation() : string
    {
        return "Lost";
    }
}
