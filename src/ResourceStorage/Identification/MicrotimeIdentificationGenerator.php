<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Identification;

/**
 * Class MicrotimeIdentificationGenerator
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class MicrotimeIdentificationGenerator implements IdentificationGenerator
{

    /**
     * @return ResourceIdentification
     * @throws \Exception
     */
    public function getUniqueResourceIdentification() : ResourceIdentification
    {
        return new ResourceIdentification((string) microtime(true));
    }
}
