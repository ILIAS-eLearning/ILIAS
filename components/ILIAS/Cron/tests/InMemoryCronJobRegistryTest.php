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

use PHPUnit\Framework\TestCase;
use ILIAS\Cron\InMemoryCronJobRegistry;
use ILIAS\Cron\CronJob;

class InMemoryCronJobRegistryTest extends TestCase
{
    public function testGetAllJobsReturnsConstructorList(): void
    {
        $a = $this->createStub(CronJob::class);
        $a->method('getId')->willReturn('a');

        $b = $this->createStub(CronJob::class);
        $b->method('getId')->willReturn('b');

        $registry = new InMemoryCronJobRegistry([$a, $b]);

        self::assertSame([$a, $b], $registry->getAllJobs());
    }

    public function testRejectsDuplicateJobIds(): void
    {
        $a = $this->createStub(CronJob::class);
        $a->method('getId')->willReturn('dup');

        $b = $this->createStub(CronJob::class);
        $b->method('getId')->willReturn('dup');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Duplicate cron job id contributed: dup');

        new InMemoryCronJobRegistry([$a, $b]);
    }

    public function testRejectsEmptyJobId(): void
    {
        $a = $this->createStub(CronJob::class);
        $a->method('getId')->willReturn('');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cron job id must not be empty.');

        new InMemoryCronJobRegistry([$a]);
    }
}
