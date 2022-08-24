<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

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
    public function register(ResourceIdentification $i, ResourceStakeholder $s): bool;

    public function deregister(ResourceIdentification $i, ResourceStakeholder $s): bool;

    /**
     * @return ResourceStakeholder[]
     */
    public function getStakeholders(ResourceIdentification $i): array;
}
