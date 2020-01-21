<?php namespace ILIAS\GlobalScreen\Identification;

/**
 * Class LostIdentification
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LostIdentification implements IdentificationInterface
{

    /**
     * @var string
     */
    private $serialized_string = "";


    /**
     * NullIdentification constructor.
     *
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
        return;
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
