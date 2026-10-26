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

namespace ILIAS\WOPI\Lock;

/**
 * Implements the lock semantics of WOPI, see
 * https://learn.microsoft.com/en-us/microsoft-365/cloud-storage-partner-program/rest/concepts#lock
 *
 * A lock expires after 30 minutes unless the WOPI client refreshes it. An expired lock is
 * treated as if the resource were unlocked.
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class LockService
{
    public const LOCK_DURATION = 30 * 60;
    public const MAX_LOCK_LENGTH = 1024;

    /**
     * @var \Closure(): int
     */
    private \Closure $clock;

    /**
     * @param \Closure(): int|null $clock returns the current unix timestamp, for testing
     */
    public function __construct(
        private LockRepository $repository,
        ?\Closure $clock = null
    ) {
        $this->clock = $clock ?? static fn(): int => time();
    }

    /**
     * @return string the current lock, an empty string if the resource is not locked
     */
    public function getLock(string $resource_id): string
    {
        return $this->current($resource_id)?->value ?? '';
    }

    /**
     * Lock: locks an unlocked resource or refreshes the lock if the same lock is passed again.
     */
    public function lock(string $resource_id, string $lock, int $user_id): LockResult
    {
        if (($invalid = $this->validate($lock)) !== null) {
            return $invalid;
        }
        $current = $this->current($resource_id);
        if ($current !== null && !$current->matches($lock)) {
            return LockResult::conflict($current->value, 'Locked by another client');
        }
        $this->store($resource_id, $lock, $user_id);

        return LockResult::success();
    }

    /**
     * UnlockAndRelock: replaces the lock, if the old lock matches the current one.
     */
    public function unlockAndRelock(string $resource_id, string $old_lock, string $lock, int $user_id): LockResult
    {
        if (($invalid = $this->validate($lock)) !== null) {
            return $invalid;
        }
        $current = $this->current($resource_id);
        if ($current === null) {
            return LockResult::conflict('', 'Not locked');
        }
        if (!$current->matches($old_lock)) {
            return LockResult::conflict($current->value, 'Lock mismatch');
        }
        $this->store($resource_id, $lock, $user_id);

        return LockResult::success();
    }

    public function refresh(string $resource_id, string $lock, int $user_id): LockResult
    {
        $current = $this->current($resource_id);
        if ($current === null) {
            return LockResult::conflict('', 'Not locked');
        }
        if (!$current->matches($lock)) {
            return LockResult::conflict($current->value, 'Lock mismatch');
        }
        $this->store($resource_id, $lock, $user_id);

        return LockResult::success();
    }

    public function unlock(string $resource_id, string $lock): LockResult
    {
        $current = $this->current($resource_id);
        if ($current === null) {
            return LockResult::conflict('', 'Not locked');
        }
        if (!$current->matches($lock)) {
            return LockResult::conflict($current->value, 'Lock mismatch');
        }
        $this->repository->delete($resource_id);

        return LockResult::success();
    }

    /**
     * PutFile: a locked resource may only be written with its lock, an unlocked one only as
     * long as it has no content yet - which is how a WOPI client fills a file created for
     * the editnew action.
     */
    public function canWrite(string $resource_id, ?string $lock, bool $is_empty): LockResult
    {
        $current = $this->current($resource_id);
        if ($current === null) {
            return $is_empty
                ? LockResult::success()
                : LockResult::conflict('', 'Not locked');
        }
        if ($lock === null || !$current->matches($lock)) {
            return LockResult::conflict($current->value, 'Lock mismatch');
        }

        return LockResult::success();
    }

    private function current(string $resource_id): ?Lock
    {
        $lock = $this->repository->find($resource_id);
        if ($lock === null) {
            return null;
        }
        if ($lock->isExpired(($this->clock)())) {
            $this->repository->delete($resource_id);
            return null;
        }

        return $lock;
    }

    private function store(string $resource_id, string $lock, int $user_id): void
    {
        $this->repository->store(
            $resource_id,
            new Lock($lock, ($this->clock)() + self::LOCK_DURATION, $user_id)
        );
    }

    private function validate(string $lock): ?LockResult
    {
        if ($lock === '' || strlen($lock) > self::MAX_LOCK_LENGTH) {
            return LockResult::conflict('', 'Invalid lock');
        }

        return null;
    }
}
