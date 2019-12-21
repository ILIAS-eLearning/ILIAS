<?php

namespace ILIAS\MainMenu\Storage\Revision;

use ILIAS\MainMenu\Storage\Identification\ResourceIdentification;
use ILIAS\MainMenu\Storage\Information\FileInformation;
use ILIAS\MainMenu\Storage\Information\Information;

/**
 * Class FileRevision
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Revision
{
    public const STATUS_ACTIVE = 1;


    /**
     * @return ResourceIdentification
     */
    public function getIdentification() : ResourceIdentification;


    /**
     * @return int
     */
    public function getVersionNumber() : int;


    public function getInformation() : Information;


    public function setInformation(Information $information);


    public function setUnavailable() : void;


    /**
     * @return bool
     */
    public function isAvailable() : bool;
}
