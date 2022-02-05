<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use PHPUnit\Framework\TestCase;

class CronJobManagerTest extends TestCase
{
    public function testCronManagerActivatesJobWhenJobWasReset() : void
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

        $cronManager = new ilCronManagerImpl(
            $repository,
            $db,
            $setting,
            $logger
        );

        $repository->expects($this->once())->method('updateJobResult')->with(
            $job,
            $user,
            $this->isInstanceOf(ilCronJobResult::class),
            true
        );

        $repository->expects($this->once())->method('resetJob')->with(
            $job
        );

        $repository->expects($this->once())->method('activateJob')->with(
            $job,
            $user,
            true
        );

        $cronManager->resetJob($job, $user);
    }
}
