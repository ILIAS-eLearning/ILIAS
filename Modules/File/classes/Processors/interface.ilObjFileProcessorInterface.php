<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Interface ilObjFileProcessorInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilObjFileProcessorInterface
{
    /**
     * Processes a given resource for the given arguments.
     */
    public function process(
        ResourceIdentification $rid,
        string $title = null,
        string $description = null,
        int $copyright_id = null
    ): void;
}
