<?php

namespace ILIAS\ResourceStorage\Stakeholder\Repository;

use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use ILIAS\ResourceStorage\Stakeholder\ResourceStakeholder;
use ILIAS\ResourceStorage\Lock\LockingRepository;
use ILIAS\ResourceStorage\Preloader\PreloadableRepository;

/**
 * Interface StakeholderRepository
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface StakeholderRepository extends LockingRepository, PreloadableRepository
{
    public function register(ResourceIdentification $i, ResourceStakeholder $s) : bool;

    public function deregister(ResourceIdentification $i, ResourceStakeholder $s) : bool;

    /**
     * @param ResourceIdentification $i
     * @return ResourceStakeholder[]
     */
    public function getStakeholders(ResourceIdentification $i) : array;
}
