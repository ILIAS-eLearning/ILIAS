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

interface ilMailAddressTypeHelper
{
    public function doesGroupNameExists(string $name): bool;

    public function getGroupObjIdByTitle(string $title): int;

    public function getInstanceByRefId(int $ref_id): ilObject;

    /**
     * @return int[]
     */
    public function getAllRefIdsForObjId(int $obj_id): array;

    public function getUserIdByLogin(string $login): int;

    public function getInstallationHost(): string;

    public function getGlobalMailSystemId(): int;

    public function receivesInternalMailsOnly(int $usr_id): bool;
}
