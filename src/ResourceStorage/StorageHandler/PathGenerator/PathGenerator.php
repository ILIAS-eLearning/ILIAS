<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\StorageHandler\PathGenerator;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class PathGenerator
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface PathGenerator
{
    public function getPathFor(ResourceIdentification $i) : string;

    public function getIdentificationFor(string $path) : ResourceIdentification;
}
