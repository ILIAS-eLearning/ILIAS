<?php

namespace ILIAS\ResourceStorage\Stakeholder\Repository;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Lock\LockingRepository;

/**
 * Interface StakeholderRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StakeholderRepository extends LockingRepository
{
    public function register(ResourceIdentification $i, ResourceStakeholder $s) : bool;

    public function deregister(ResourceIdentification $i, ResourceStakeholder $s) : bool;

    /**
     * @param ResourceIdentification $i
     * @return ResourceStakeholder[]
     */
    public function getStakeholders(ResourceIdentification $i) : array;
}
