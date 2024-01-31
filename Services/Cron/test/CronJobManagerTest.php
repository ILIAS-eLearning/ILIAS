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

class CronJobManagerTest extends TestCase
{
    private function createClockFactoryMock(): \ILIAS\Data\Clock\ClockFactory
    {
        $clock_factory = $this->createMock(ILIAS\Data\Clock\ClockFactory::class);
        $now = new DateTimeImmutable('@' . time());
        $clock_factory->method('system')->willReturn(
            new class ($now) implements \ILIAS\Data\Clock\ClockInterface {
                private DateTimeImmutable $now;

                public function __construct(DateTimeImmutable $now)
                {
                    $this->now = $now;
                }

                public function now(): DateTimeImmutable
                {
                    return $this->now;
                }
            }
        );

        return $clock_factory;
    }

    public function testCronManagerActivatesJobWhenJobWasReset(): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $setting = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(ilLogger::class)->disableOriginalConstructor()->getMock();
        $repository = $this->createMock(ilCronJobRepository::class);
        $job = $this->createMock(ilCronJob::class);
        $user = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();

        $clock_factory = $this->createClockFactoryMock();

        $cronManager = new ilCronManagerImpl(
            $repository,
            $db,
            $setting,
            $logger,
            $clock_factory
        );

        $repository->expects($this->once())->method('updateJobResult')->with(
            $job,
            $clock_factory->system()->now(),
            $user,
            $this->isInstanceOf(ilCronJobResult::class),
            true
        );

        $repository->expects($this->once())->method('resetJob')->with(
            $job
        );

        $repository->expects($this->once())->method('activateJob')->with(
            $job,
            $clock_factory->system()->now(),
            $user,
            true
        );

        $job->expects($this->once())->method('activationWasToggled')->with(
            $db,
            $setting,
            true
        );

        $cronManager->resetJob($job, $user);
    }

    public function testCronManagerNotifiesJobWhenJobGetsActivated(): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $setting = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(ilLogger::class)->disableOriginalConstructor()->getMock();
        $repository = $this->createMock(ilCronJobRepository::class);
        $job = $this->createMock(ilCronJob::class);
        $user = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();

        $clock_factory = $this->createClockFactoryMock();

        $cronManager = new ilCronManagerImpl(
            $repository,
            $db,
            $setting,
            $logger,
            $clock_factory
        );

        $repository->expects($this->exactly(2))->method('activateJob')->withConsecutive(
            [$job, $clock_factory->system()->now(), $user, true],
            [$job, $clock_factory->system()->now(), $user, false]
        );

        $job->expects($this->exactly(2))->method('activationWasToggled')->with(
            $db,
            $setting,
            true
        );

        $cronManager->activateJob($job, $user, true);
        $cronManager->activateJob($job, $user, false);
    }

    public function testCronManagerNotifiesJobWhenJobGetsDeactivated(): void
    {
        $db = $this->createMock(ilDBInterface::class);
        $setting = $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock();
        $logger = $this->getMockBuilder(ilLogger::class)->disableOriginalConstructor()->getMock();
        $repository = $this->createMock(ilCronJobRepository::class);
        $job = $this->createMock(ilCronJob::class);
        $user = $this->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();

        $clock_factory = $this->createClockFactoryMock();

        $cronManager = new ilCronManagerImpl(
            $repository,
            $db,
            $setting,
            $logger,
            $clock_factory
        );

        $repository->expects($this->exactly(2))->method('deactivateJob')->withConsecutive(
            [$job, $clock_factory->system()->now(), $user, true],
            [$job, $clock_factory->system()->now(), $user, false]
        );

        $job->expects($this->exactly(2))->method('activationWasToggled')->with(
            $db,
            $setting,
            false
        );

        $cronManager->deactivateJob($job, $user, true);
        $cronManager->deactivateJob($job, $user, false);
    }
}
