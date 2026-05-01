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

namespace ILIAS\WebDAV\Lock;

interface LocksRepository
{
    public function existsFor(string $token): bool;

    public function maybeGetLockFromToken(string $token): ?LockObject;

    public function maybeGetLockFromObjId(int $obj_id): ?LockObject;

    public function save(LockObject $ilias_lock): void;

    public function remove(string $token): int;

    public function purgeExpired(): int;

    public function updateLocks(int $old_obj_id, int $new_obj_id): int;
}
