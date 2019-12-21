<?php

namespace ILIAS\MainMenu\Storage\Identification;

/**
 * Class UniqueIDIdentificationGenerator
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface IdentificationGenerator
{

    /**
     * @return ResourceIdentification
     * @throws \Exception
     */
    public function getUniqueResourceIdentification() : ResourceIdentification;
}
