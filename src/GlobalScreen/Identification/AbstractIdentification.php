<?php namespace ILIAS\GlobalScreen\Identification;

use ILIAS\GlobalScreen\Identification\Serializer\SerializerInterface;
use ILIAS\GlobalScreen\Provider\Provider;

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
 * Class AbstractIdentification
 * @package ILIAS\GlobalScreen\Identification
 */
abstract class AbstractIdentification implements IdentificationInterface
{
    protected SerializerInterface $serializer;
    protected string $provider_presentation_name;
    protected string $internal_identifier = '';
    protected string $classname = '';
    
    /**
     * CoreIdentification constructor.
     * @param string              $internal_identifier
     * @param string              $classname
     * @param SerializerInterface $serializer
     * @param string              $provider_presentation_name
     */
    public function __construct(string $internal_identifier, string $classname, SerializerInterface $serializer, string $provider_presentation_name)
    {
        $this->provider_presentation_name = $provider_presentation_name;
        $this->serializer                 = $serializer;
        $this->internal_identifier        = $internal_identifier;
        $this->classname                  = $classname;
    }
    
    /**
     * @inheritDoc
     */
    public function serialize()
    {
        return $this->serializer->serialize($this);
    }
    
    /**
     * @inheritDoc
     */
    public function unserialize($serialized)
    {
        throw new \LogicException("Please use the identification factory to unserialize");
    }
    
    /**
     * @inheritDoc
     */
    public function getClassName() : string
    {
        return $this->classname;
    }
    
    /**
     * @inheritDoc
     */
    public function getInternalIdentifier() : string
    {
        return $this->internal_identifier;
    }
    
    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation() : string
    {
        global $DIC;
        /**
         * @var $provider Provider
         */
        $provider = new $this->classname($DIC);
        
        return $provider->getProviderNameForPresentation();
    }
}
