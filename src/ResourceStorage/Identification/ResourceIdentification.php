<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Identification;

use Serializable;

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
 * Interface Identification
 *
 * @internal
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ResourceIdentification implements Serializable
{
    private string $unique_id;


    /**
     * ResourceIdentification constructor.
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
    public function __toString() : string
    {
        return $this->serialize();
    }
}
