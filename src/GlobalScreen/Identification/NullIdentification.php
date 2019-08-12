<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class NullIdentification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NullIdentification implements IdentificationInterface
{

    /**
     * @var IdentificationInterface
     */
    protected $wrapped_identification = null;


    /**
     * NullIdentification constructor.
     *
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
        return;
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
