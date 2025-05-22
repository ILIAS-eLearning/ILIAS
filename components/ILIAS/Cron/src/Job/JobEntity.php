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

namespace ILIAS\Cron\Job;

use ILIAS\Cron\Job\Schedule\JobScheduleType;

class JobEntity
{
    private string $job_id;
    private string $component;
    private ?JobScheduleType $schedule_type;
    private int $schedule_value;
    private int $job_status;
    private int $job_status_usr_id;
    private int $job_status_type;
    private int $job_status_timestamp;
    private int $job_result_status;
    private int $job_result_usr_id;
    private string $job_result_code;
    private string $job_result_message;
    private int $job_result_type;
    private int $job_result_timestamp;
    private string $class;
    private string $path;
    private int $running_timestamp;
    private int $job_result_duration;
    private int $alive_timestamp;

    /**
     * @param array<string, mixed> $record
     */
    public function __construct(
        private readonly \ILIAS\Cron\CronJob $job,
        array $record,
        private readonly bool $isPlugin = false
    ) {
        $this->mapRecord($record);
    }

    /**
     * @param array<string, mixed> $record
     */
    private function mapRecord(array $record): void
    {
        $this->job_id = (string) $record['job_id'];
        $this->component = (string) $record['component'];
        $this->schedule_type = is_numeric($record['schedule_type']) ? JobScheduleType::tryFrom(
            (int) $record['schedule_type']
        ) : null;
        $this->schedule_value = (int) $record['schedule_value'];
        $this->job_status = (int) $record['job_status'];
        $this->job_status_usr_id = (int) $record['job_status_user_id'];
        $this->job_status_type = (int) $record['job_status_type'];
        $this->job_status_timestamp = (int) $record['job_status_ts'];
        $this->job_result_status = (int) $record['job_result_status'];
        $this->job_result_usr_id = (int) $record['job_result_user_id'];
        $this->job_result_code = (string) $record['job_result_code'];
        $this->job_result_message = (string) $record['job_result_message'];
        $this->job_result_type = (int) $record['job_result_type'];
        $this->job_result_timestamp = (int) $record['job_result_ts'];
        $this->class = (string) $record['class'];
        $this->path = (string) $record['path'];
        $this->running_timestamp = (int) $record['running_ts'];
        $this->job_result_duration = (int) $record['job_result_dur'];
        $this->alive_timestamp = (int) $record['alive_ts'];
    }

    public function getJob(): \ILIAS\Cron\CronJob
    {
        return $this->job;
    }

    public function getJobId(): string
    {
        return $this->job_id;
    }

    public function getEffectiveJobId(): string
    {
        $job_id = $this->getJobId();
        if ($this->isPlugin()) {
            $job_id = 'pl__' . $this->getComponent() . '__' . $job_id;
        }

        return $job_id;
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function getScheduleType(): ?JobScheduleType
    {
        return $this->schedule_type;
    }

    public function getScheduleValue(): int
    {
        return $this->schedule_value;
    }

    public function getJobStatus(): int
    {
        return $this->job_status;
    }

    public function getJobStatusUsrId(): int
    {
        return $this->job_status_usr_id;
    }

    public function getJobStatusType(): int
    {
        return $this->job_status_type;
    }

    public function getJobStatusTimestamp(): int
    {
        return $this->job_status_timestamp;
    }

    public function getJobResultStatus(): int
    {
        return $this->job_result_status;
    }

    public function getJobResultUsrId(): int
    {
        return $this->job_result_usr_id;
    }

    public function getJobResultCode(): string
    {
        return $this->job_result_code;
    }

    public function getJobResultMessage(): string
    {
        return $this->job_result_message;
    }

    public function getJobResultType(): int
    {
        return $this->job_result_type;
    }

    public function getJobResultTimestamp(): int
    {
        return $this->job_result_timestamp;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getRunningTimestamp(): int
    {
        return $this->running_timestamp;
    }

    public function getJobResultDuration(): int
    {
        return $this->job_result_duration;
    }

    public function getAliveTimestamp(): int
    {
        return $this->alive_timestamp;
    }

    public function isPlugin(): bool
    {
        return $this->isPlugin;
    }

    public function getEffectiveScheduleType(): JobScheduleType
    {
        $type = $this->getScheduleType();
        if (!$type || !$this->getJob()->hasFlexibleSchedule()) {
            $type = $this->getJob()->getDefaultScheduleType();
        }

        return $type;
    }

    public function getEffectiveScheduleValue(): int
    {
        $type = $this->getScheduleType();
        $value = $this->getScheduleValue();
        if (!$type || !$this->getJob()->hasFlexibleSchedule()) {
            $value = (int) $this->getJob()->getDefaultScheduleValue();
        }

        return $value;
    }

    public function getEffectiveTitle(): string
    {
        $id = $this->getJobId();
        if ($this->isPlugin()) {
            $id = 'pl__' . $this->getComponent() . '__' . $id;
        }

        $title = $this->getJob()->getTitle();
        if ($title === '') {
            $title = $id;
        }

        return $title;
    }
}
