<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Resource\Stakeholder;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Interface ResourceStakeholder
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ResourceStakeholder
{

    /**
     * @return string
     */
    public function getId() : string;


    /**
     * @return string
     */
    public function getConsumerNameForPresentation() : string;


    /**
     * @return string
     */
    public function getFullyQualifiedClassName() : string;


    /**
     * @param ResourceIdentification $identification
     *
     * @return bool
     */
    public function isResourceInUse(ResourceIdentification $identification) : bool;


    /**
     * @param ResourceIdentification $identification
     */
    public function resourceHasBeenDeleted(ResourceIdentification $identification) : void;


    /**
     * @param ResourceIdentification $identification
     *
     * @return int
     */
    public function getOwnerOfResource(ResourceIdentification $identification) : int;
}
