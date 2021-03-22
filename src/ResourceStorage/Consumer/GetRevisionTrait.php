<?php

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Revision\Revision;
use OutOfBoundsException;

/**
 * Trait GetRevisionTrait
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait GetRevisionTrait
{
    /**
     * @return Revision
     * @throws OutOfBoundsException
     */
    protected function getRevision() : Revision
    {
        if ($this->revision_number !== null) {
            if ($this->resource->hasSpecificRevision($this->revision_number)) {
                $revision = $this->resource->getSpecificRevision($this->revision_number);
            } else {
                throw new OutOfBoundsException("there is no version $this->revision_number of resource {$this->resource->getIdentification()->serialize()}");
            }
        } else {
            $revision = $this->resource->getCurrentRevision();
        }
        return $revision;
    }

}
