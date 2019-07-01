<?php namespace ILIAS\GlobalScreen\Scope\Tool\Context;

use ILIAS\Data\ReferenceId;
use ILIAS\GlobalScreen\Scope\Tool\Context\AdditionalData\Collection;

/**
 * Class BasicToolContext
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BasicToolContext implements ToolContext
{

    /**
     * @var ReferenceId
     */
    protected $reference_id;
    /**
     * @var Collection
     */
    protected $additional_data;
    /**
     * @var string
     */
    protected $context_identifier = '';


    /**
     * BasicToolContext constructor.
     *
     * @param string $context_identifier
     */
    public function __construct(string $context_identifier)
    {
        static $initialised;
        if ($initialised !== null) {
            throw new \LogicException("only one instance of a view can exist");
        }
        $this->context_identifier = $context_identifier;
        $this->additional_data = new Collection();
        $this->reference_id = new ReferenceId(0);
    }


    /**
     * @inheritDoc
     */
    public function hasReferenceId() : bool
    {
        return $this->reference_id instanceof ReferenceId && $this->reference_id->toInt() > 0;
    }


    /**
     * @inheritDoc
     */
    public function getReferenceId() : ReferenceId
    {
        return $this->reference_id;
    }


    /**
     * @inheritDoc
     */
    public function withReferenceId(ReferenceId $reference_id) : ToolContext
    {
        $clone = clone $this;
        $clone->reference_id = $reference_id;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function withAdditionalData(Collection $collection) : ToolContext
    {
        $clone = clone $this;
        $clone->additional_data = $collection;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getAdditionalData() : Collection
    {
        return $this->additional_data;
    }


    /**
     * @inheritDoc
     */
    public function addAdditionalData(string $key, $value) : ToolContext
    {
        $this->additional_data->add($key, $value);

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getUniqueContextIdentifier() : string
    {
        return $this->context_identifier;
    }
}
