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

use PHPUnit\Framework\TestCase;

final class LockServiceTest extends TestCase
{
    private const RID = 'resource';
    private const USER = 6;

    private int $now = 1_000_000;
    private LockRepository $repository;
    private LockService $service;

    protected function setUp(): void
    {
        $this->repository = new class () implements LockRepository {
            /** @var array<string, Lock> */
            public array $locks = [];

            public function find(string $resource_id): ?Lock
            {
                return $this->locks[$resource_id] ?? null;
            }

            public function store(string $resource_id, Lock $lock): void
            {
                $this->locks[$resource_id] = $lock;
            }

            public function delete(string $resource_id): void
            {
                unset($this->locks[$resource_id]);
            }
        };
        $this->service = new LockService($this->repository, fn(): int => $this->now);
    }

    private function assertConflict(LockResult $result, string $current_lock): void
    {
        $this->assertFalse($result->success);
        $this->assertSame($current_lock, $result->current_lock);
        $this->assertNotNull($result->reason);
    }

    public function testLockUnlockedResource(): void
    {
        $this->assertTrue($this->service->lock(self::RID, 'A', self::USER)->success);
        $this->assertSame('A', $this->service->getLock(self::RID));
    }

    public function testLockAgainWithSameLockRefreshesIt(): void
    {
        $this->service->lock(self::RID, 'A', self::USER);
        $this->now += 1000;

        $this->assertTrue($this->service->lock(self::RID, 'A', self::USER)->success);
        $this->assertSame($this->now + LockService::LOCK_DURATION, $this->repository->find(self::RID)->expires);
    }

    public function testLockWithOtherLockConflicts(): void
    {
        $this->service->lock(self::RID, 'A', self::USER);

        $this->assertConflict($this->service->lock(self::RID, 'B', self::USER), 'A');
        $this->assertSame('A', $this->service->getLock(self::RID));
    }

    public function testInvalidLocksAreRejected(): void
    {
        $this->assertConflict($this->service->lock(self::RID, '', self::USER), '');
        $this->assertConflict(
            $this->service->lock(self::RID, str_repeat('x', LockService::MAX_LOCK_LENGTH + 1), self::USER),
            ''
        );
        $this->assertTrue(
            $this->service->lock(self::RID, str_repeat('x', LockService::MAX_LOCK_LENGTH), self::USER)->success
        );
    }

    public function testExpiredLockCountsAsUnlocked(): void
    {
        $this->service->lock(self::RID, 'A', self::USER);
        $this->now += LockService::LOCK_DURATION;

        $this->assertSame('', $this->service->getLock(self::RID));
        $this->assertTrue($this->service->lock(self::RID, 'B', self::USER)->success);
        $this->assertSame('B', $this->service->getLock(self::RID));
    }

    public function testUnlockAndRelock(): void
    {
        $this->service->lock(self::RID, 'A', self::USER);

        $this->assertConflict($this->service->unlockAndRelock(self::RID, 'X', 'B', self::USER), 'A');
        $this->assertTrue($this->service->unlockAndRelock(self::RID, 'A', 'B', self::USER)->success);
        $this->assertSame('B', $this->service->getLock(self::RID));
    }

    public function testUnlockAndRelockOnUnlockedResourceConflicts(): void
    {
        $this->assertConflict($this->service->unlockAndRelock(self::RID, 'A', 'B', self::USER), '');
    }

    public function testRefresh(): void
    {
        $this->assertConflict($this->service->refresh(self::RID, 'A', self::USER), '');

        $this->service->lock(self::RID, 'A', self::USER);
        $this->now += 1000;

        $this->assertConflict($this->service->refresh(self::RID, 'B', self::USER), 'A');
        $this->assertTrue($this->service->refresh(self::RID, 'A', self::USER)->success);
        $this->assertSame($this->now + LockService::LOCK_DURATION, $this->repository->find(self::RID)->expires);
    }

    public function testUnlock(): void
    {
        $this->assertConflict($this->service->unlock(self::RID, 'A'), '');

        $this->service->lock(self::RID, 'A', self::USER);

        $this->assertConflict($this->service->unlock(self::RID, 'B'), 'A');
        $this->assertTrue($this->service->unlock(self::RID, 'A')->success);
        $this->assertSame('', $this->service->getLock(self::RID));
    }

    public function testUnlockedResourceMayOnlyBeWrittenWhileEmpty(): void
    {
        $this->assertTrue($this->service->canWrite(self::RID, null, true)->success);
        $this->assertConflict($this->service->canWrite(self::RID, null, false), '');
    }

    public function testLockedResourceMayOnlyBeWrittenWithItsLock(): void
    {
        $this->service->lock(self::RID, 'A', self::USER);

        $this->assertTrue($this->service->canWrite(self::RID, 'A', false)->success);
        $this->assertConflict($this->service->canWrite(self::RID, 'B', false), 'A');
        $this->assertConflict($this->service->canWrite(self::RID, null, true), 'A');
    }
}
