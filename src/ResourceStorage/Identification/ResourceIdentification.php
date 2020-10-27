<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Identification;

use Serializable;

/**
 * Interface Identification
 *
 * @internal
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ResourceIdentification implements Serializable
{

    /**
     * @var string
     */
    private $unique_id;


    /**
     * ResourceIdentification constructor.
     *
     * @param string $unique_id
     */
    public function __construct(string $unique_id)
    {
        $this->unique_id = $unique_id;
    }


    /**
     * @inheritDoc
     */
    public function serialize() : string
    {
        return $this->unique_id;
    }


    /**
     * @inheritDoc
     */
    public function unserialize($serialized) : void
    {
        $this->unique_id = $serialized;
    }


    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->serialize();
    }
}
