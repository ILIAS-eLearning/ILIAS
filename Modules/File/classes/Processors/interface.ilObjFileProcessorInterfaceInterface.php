<?php

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Interface ilObjFileProcessorInterface
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
interface ilObjFileProcessorInterface
{
    public const OPTION_FILENAME = 'title';
    public const OPTION_DESCRIPTION = 'description';

    /**
     * @var string[] available options for ilObjFile
     */
    public const OPTIONS = [
        self::OPTION_FILENAME,
        self::OPTION_DESCRIPTION,
    ];

    /**
     * Processes a given resource for the given arguments.
     * @param array<string, mixed>   $options
     * @see ilObjFileProcessorInterface::OPTIONS
     */
    public function process(ResourceIdentification $rid, array $options = []) : void;
}
