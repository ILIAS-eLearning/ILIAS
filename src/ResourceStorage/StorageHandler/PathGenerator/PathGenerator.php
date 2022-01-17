<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\StorageHandler\PathGenerator;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

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
 * Class PathGenerator
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface PathGenerator
{
    public function getPathFor(ResourceIdentification $i) : string;

    public function getIdentificationFor(string $path) : ResourceIdentification;
}
