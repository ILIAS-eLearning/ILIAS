<?php

declare(strict_types=1);

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
 *********************************************************************/

namespace ILIAS\ResourceStorage\StorageHandler\PathGenerator;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class UUIDBasedPathGenerator
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 * @depracated Only used in FileSystemStorageHandler which is deprecated as well
 * @internal
 */
class UUIDBasedPathGenerator implements PathGenerator
{
    public function getPathFor(ResourceIdentification $i): string
    {
        return str_replace("-", "/", $i->serialize());
    }

    public function getIdentificationFor(string $path): ResourceIdentification
    {
        return new ResourceIdentification(str_replace("/", "-", $path));
    }
}
