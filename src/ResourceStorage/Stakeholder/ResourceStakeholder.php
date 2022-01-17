<?php declare(strict_types=1);

namespace ILIAS\ResourceStorage\Stakeholder;

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
 * Interface ResourceStakeholder
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ResourceStakeholder
{

    /**
     * @return string not longer than 64 characters
     */
    public function getId() : string;

    public function getConsumerNameForPresentation() : string;

    /**
     * @return string not longer than 250 characters
     */
    public function getFullyQualifiedClassName() : string;

    public function isResourceInUse(ResourceIdentification $identification) : bool;

    /**
     * @return bool true: if the Stakeholder could handle the deletion; false: if the Stakeholder could not handle
     * the deletion of the resource.
     */
    public function resourceHasBeenDeleted(ResourceIdentification $identification) : bool;

    public function getOwnerOfResource(ResourceIdentification $identification) : int;

    public function getOwnerOfNewResources() : int;
}
