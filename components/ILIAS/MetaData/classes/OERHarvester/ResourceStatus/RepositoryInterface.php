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
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\MetaData\OERHarvester\ResourceStatus;

interface RepositoryInterface
{
    public function isHarvestingBlocked(int $obj_id): bool;

    public function setHarvestingBlocked(int $obj_id, bool $blocked): void;

    public function isAlreadyHarvested(int $obj_id): bool;

    /**
     * @return int[]
     */
    public function getAllHarvestedObjIDs(): \Generator;

    public function getHarvestRefID(int $obj_id): int;

    public function setHarvestRefID(int $obj_id, int $harvested_ref_id): void;

    public function deleteHarvestRefID(int $obj_id): void;

    /**
     * @return int[]
     */
    public function filterOutBlockedObjects(int ...$obj_ids): \Generator;

    public function deleteStatus(int $obj_id): void;
}
