<?php

namespace ILIAS\ResourceStorage\Revision;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Information\Information;

/**
 * Class FileRevision
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

    public function getOwnerId() : int;

    public function setTitle(string $title) : Revision;

    public function getTitle() : string;
}
