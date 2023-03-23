<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function process(ResourceIdentification $rid, array $options = []): void;
}
