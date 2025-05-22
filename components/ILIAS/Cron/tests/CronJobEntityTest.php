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
use ILIAS\Cron\Job\Schedule\JobScheduleType;
use ILIAS\Cron\Job\JobEntity;
use ILIAS\Cron\Job\JobResult;
use ILIAS\Cron\CronJob;
use PHPUnit\Framework\Attributes\Depends;

class CronJobEntityTest extends TestCase
{
    private function getEntity(
        ?CronJob $job_instance = null,
        ?int $schedule_type = null,
        int $schedule_value = 5,
        bool $is_plugin = false
    ): JobEntity {
        $job_instance ??= $this->createMock(CronJob::class);

        if ($schedule_type === null) {
            $schedule_type = JobScheduleType::IN_MINUTES->value;
        }

        return new JobEntity($job_instance, [
            'job_id' => 'phpunit',
            'component' => 'phpunit',
            'schedule_type' => $schedule_type,
            'schedule_value' => $schedule_value,
            'job_status' => 1,
            'job_status_user_id' => 6,
            'job_status_type' => 1,
            'job_status_ts' => time(),
            'job_result_status' => JobResult::STATUS_OK,
            'job_result_user_id' => 6,
            'job_result_code' => JobResult::CODE_NO_RESULT,
            'job_result_message' => 'msg',
            'job_result_type' => 1,
            'job_result_ts' => time(),
            'class' => 'Job',
            'path' => '/',
            'running_ts' => time(),
            'job_result_dur' => time(),
            'alive_ts' => time(),
        ], $is_plugin);
    }

    public function testEntityCollectionCanBeCreatedWithItems(): \ILIAS\Cron\Job\Collection\JobEntities
    {
        $entities = new \ILIAS\Cron\Job\Collection\JobEntities($this->getEntity(), $this->getEntity());

        $this->assertCount(2, $entities->toArray());

        return $entities;
    }

    #[Depends('testEntityCollectionCanBeCreatedWithItems')]
    public function testCollectionCanBeChanged(
        \ILIAS\Cron\Job\Collection\JobEntities $entities
    ): \ILIAS\Cron\Job\Collection\JobEntities {
        $entities->add($this->getEntity());

        $this->assertCount(3, $entities->toArray());

        return $entities;
    }

    #[Depends('testCollectionCanBeChanged')]
    public function testCollectionCanBeFilteredAndSliced(\ILIAS\Cron\Job\Collection\JobEntities $entities): void
    {
        $this->assertCount(0, $entities->filter(static function (JobEntity $entity): bool {
            return $entity->getJobId() !== 'phpunit';
        }));

        $this->assertCount(1, $entities->slice(1, 1));
    }

    public function testEffectiveScheduleCanBeDetermined(): void
    {
        $job_instance = $this->createMock(CronJob::class);
        $job_instance->method('hasFlexibleSchedule')->willReturn(true);

        $entity = $this->getEntity($job_instance);
        $this->assertSame(JobScheduleType::IN_MINUTES, $entity->getEffectiveScheduleType());
        $this->assertSame(5, $entity->getEffectiveScheduleValue());

        $another_job_instance = $this->createMock(CronJob::class);
        $another_job_instance->method('hasFlexibleSchedule')->willReturn(false);
        $another_job_instance->method('getDefaultScheduleType')->willReturn(
            JobScheduleType::IN_HOURS
        );
        $another_job_instance->method('getDefaultScheduleValue')->willReturn(5);

        $another_entity = $this->getEntity($another_job_instance, JobScheduleType::DAILY->value);
        $this->assertSame(JobScheduleType::IN_HOURS, $another_entity->getEffectiveScheduleType());
        $this->assertSame(5, $another_entity->getEffectiveScheduleValue());

        $yet_another_job_instance = $this->createMock(CronJob::class);
        $yet_another_job_instance->method('hasFlexibleSchedule')->willReturn(true);
        $yet_another_job_instance->method('getDefaultScheduleType')->willReturn(
            JobScheduleType::IN_HOURS
        );
        $yet_another_job_instance->method('getDefaultScheduleValue')->willReturn(5);

        $yet_another_entity = $this->getEntity($yet_another_job_instance, 0);
        $this->assertSame(JobScheduleType::IN_HOURS, $yet_another_entity->getEffectiveScheduleType());
        $this->assertSame(5, $yet_another_entity->getEffectiveScheduleValue());
    }
}
