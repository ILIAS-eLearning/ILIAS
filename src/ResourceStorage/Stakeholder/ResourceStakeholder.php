<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Stakeholder;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Interface ResourceStakeholder
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ResourceStakeholder
{

    /**
     * @return string not longer than 64 characters
     */
    public function getId() : string;

    /**
     * @return string
     */
    public function getConsumerNameForPresentation() : string;

    /**
     * @return string not longer than 250 characters
     */
    public function getFullyQualifiedClassName() : string;

    /**
     * @param ResourceIdentification $identification
     * @return bool
     */
    public function isResourceInUse(ResourceIdentification $identification) : bool;

    /**
     * @param ResourceIdentification $identification
     * @return bool true: if the Stakeholder could handle the deletion; false: if the Stakeholder could not handle
     * the deletion of the resource.
     */
    public function resourceHasBeenDeleted(ResourceIdentification $identification) : bool;

    /**
     * @param ResourceIdentification $identification
     * @return int
     */
    public function getOwnerOfResource(ResourceIdentification $identification) : int;

    /**
     * @return int
     */
    public function getOwnerOfNewResources() : int;
}
