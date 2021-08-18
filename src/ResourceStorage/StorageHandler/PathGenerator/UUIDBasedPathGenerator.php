<?php declare(strict_types=1);

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
    public function getPathFor(ResourceIdentification $i) : string
    {
        return str_replace("-", "/", $i->serialize());
    }

    public function getIdentificationFor(string $path) : ResourceIdentification
    {
        return new ResourceIdentification(str_replace("/", "-", $path));
    }

}
