<?php

namespace ILIAS\ResourceStorage\Consumer;

use ILIAS\ResourceStorage\Revision\Revision;
use OutOfBoundsException;

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
 * Trait GetRevisionTrait
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait GetRevisionTrait
{
    /**
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
