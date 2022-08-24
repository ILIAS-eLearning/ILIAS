<?php

declare(strict_types=1);

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

class ilCronJobEntity
{
    private ilCronJob $job;
    private bool $isPlugin;
    private string $jobId;
    private string $component;
    private int $scheduleType;
    private int $scheduleValue;
    private int $jobStatus;
    private int $jobStatusUsrId;
    private int $jobStatusType;
    private int $jobStatusTimestamp;
    private int $jobResultStatus;
    private int $jobResultUsrId;
    private string $jobResultCode;
    private string $jobResultMessage;
    private int $jobResultType;
    private int $jobResultTimestamp;
    private string $class;
    private string $path;
    private int $runningTimestamp;
    private int $jobResultDuration;
    private int $aliveTimestamp;

    /**
     * ilCronJobEntity constructor.
     * @param ilCronJob $job
     * @param array<string, mixed> $record
     * @param bool $isPlugin
     */
    public function __construct(ilCronJob $job, array $record, bool $isPlugin = false)
    {
        $this->job = $job;
        $this->isPlugin = $isPlugin;
        $this->mapRecord($record);
    }

    /**
     * @param array<string, mixed> $record
     */
    private function mapRecord(array $record): void
    {
        $this->jobId = (string) $record['job_id'];
        $this->component = (string) $record['component'];
        $this->scheduleType = (int) $record['schedule_type'];
        $this->scheduleValue = (int) $record['schedule_value'];
        $this->jobStatus = (int) $record['job_status'];
        $this->jobStatusUsrId = (int) $record['job_status_user_id'];
        $this->jobStatusType = (int) $record['job_status_type'];
        $this->jobStatusTimestamp = (int) $record['job_status_ts'];
        $this->jobResultStatus = (int) $record['job_result_status'];
        $this->jobResultUsrId = (int) $record['job_result_user_id'];
        $this->jobResultCode = (string) $record['job_result_code'];
        $this->jobResultMessage = (string) $record['job_result_message'];
        $this->jobResultType = (int) $record['job_result_type'];
        $this->jobResultTimestamp = (int) $record['job_result_ts'];
        $this->class = (string) $record['class'];
        $this->path = (string) $record['path'];
        $this->runningTimestamp = (int) $record['running_ts'];
        $this->jobResultDuration = (int) $record['job_result_dur'];
        $this->aliveTimestamp = (int) $record['alive_ts'];
    }

    public function getJob(): ilCronJob
    {
        return $this->job;
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getComponent(): string
    {
        return $this->component;
    }

    public function getScheduleType(): int
    {
        return $this->scheduleType;
    }

    public function getScheduleValue(): int
    {
        return $this->scheduleValue;
    }

    public function getJobStatus(): int
    {
        return $this->jobStatus;
    }

    public function getJobStatusUsrId(): int
    {
        return $this->jobStatusUsrId;
    }

    public function getJobStatusType(): int
    {
        return $this->jobStatusType;
    }

    public function getJobStatusTimestamp(): int
    {
        return $this->jobStatusTimestamp;
    }

    public function getJobResultStatus(): int
    {
        return $this->jobResultStatus;
    }

    public function getJobResultUsrId(): int
    {
        return $this->jobResultUsrId;
    }

    public function getJobResultCode(): string
    {
        return $this->jobResultCode;
    }

    public function getJobResultMessage(): string
    {
        return $this->jobResultMessage;
    }

    public function getJobResultType(): int
    {
        return $this->jobResultType;
    }

    public function getJobResultTimestamp(): int
    {
        return $this->jobResultTimestamp;
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
        return $this->runningTimestamp;
    }

    public function getJobResultDuration(): int
    {
        return $this->jobResultDuration;
    }

    public function getAliveTimestamp(): int
    {
        return $this->aliveTimestamp;
    }

    public function isPlugin(): bool
    {
        return $this->isPlugin;
    }

    public function getEffectiveScheduleType(): int
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
