<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCronJobEntity
 */
class ilCronJobEntity
{
    /** @var ilCronJob */
    private $job;
    /** @var bool */
    private $isPlugin;

    /** @var string */
    private $jobId;
    /** @var string */
    private $component;
    /** @var int */
    private $scheduleType;
    /** @var int */
    private $scheduleValue;
    /** @var int */
    private $jobStatus;
    /** @var int */
    private $jobStatusUsrId;
    /** @var int */
    private $jobStatusType;
    /** @var int */
    private $jobStatusTimestamp;
    /** @var int */
    private $jobResultStatus;
    /** @var int */
    private $jobResultUsrId;
    /** @var string */
    private $jobResultCode;
    /** @var string */
    private $jobResultMessage;
    /** @var int */
    private $jobResultType;
    /** @var int */
    private $jobResultTimestamp;
    /** @var string */
    private $class;
    /** @var string */
    private $path;
    /** @var int */
    private $runningTimestamp;
    /** @var int */
    private $jobResultDuration;
    /** @var int */
    private $aliveTimestamp;

    /**
     * ilCronJobEntity constructor.
     * @param ilCronJob $job
     * @param array $record
     * @param bool $isPlugin
     */
    public function __construct(ilCronJob $job, array $record, bool $isPlugin = false)
    {
        $this->job = $job;
        $this->isPlugin = $isPlugin;
        $this->mapRecord($record);
    }

    /**
     * @param array<string, null|string> $record
     */
    private function mapRecord(array $record) : void
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

    /**
     * @return ilCronJob
     */
    public function getJob() : ilCronJob
    {
        return $this->job;
    }

    /**
     * @return string
     */
    public function getJobId() : string
    {
        return $this->jobId;
    }

    /**
     * @return string
     */
    public function getComponent() : string
    {
        return $this->component;
    }

    /**
     * @return int
     */
    public function getScheduleType() : int
    {
        return $this->scheduleType;
    }

    /**
     * @return int
     */
    public function getScheduleValue() : int
    {
        return $this->scheduleValue;
    }

    /**
     * @return int
     */
    public function getJobStatus() : int
    {
        return $this->jobStatus;
    }

    /**
     * @return int
     */
    public function getJobStatusUsrId() : int
    {
        return $this->jobStatusUsrId;
    }

    /**
     * @return int
     */
    public function getJobStatusType() : int
    {
        return $this->jobStatusType;
    }

    /**
     * @return int
     */
    public function getJobStatusTimestamp() : int
    {
        return $this->jobStatusTimestamp;
    }

    /**
     * @return int
     */
    public function getJobResultStatus() : int
    {
        return $this->jobResultStatus;
    }

    /**
     * @return int
     */
    public function getJobResultUsrId() : int
    {
        return $this->jobResultUsrId;
    }

    /**
     * @return string
     */
    public function getJobResultCode() : string
    {
        return $this->jobResultCode;
    }

    /**
     * @return string
     */
    public function getJobResultMessage() : string
    {
        return $this->jobResultMessage;
    }

    /**
     * @return int
     */
    public function getJobResultType() : int
    {
        return $this->jobResultType;
    }

    /**
     * @return int
     */
    public function getJobResultTimestamp() : int
    {
        return $this->jobResultTimestamp;
    }

    /**
     * @return string
     */
    public function getClass() : string
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getPath() : string
    {
        return $this->path;
    }

    /**
     * @return int
     */
    public function getRunningTimestamp() : int
    {
        return $this->runningTimestamp;
    }

    /**
     * @return int
     */
    public function getJobResultDuration() : int
    {
        return $this->jobResultDuration;
    }

    /**
     * @return int
     */
    public function getAliveTimestamp() : int
    {
        return $this->aliveTimestamp;
    }

    /**
     * @return bool
     */
    public function isPlugin() : bool
    {
        return $this->isPlugin;
    }

    /**
     * @return int
     */
    public function getEffectiveScheduleType() : int
    {
        $type = $this->getScheduleType();
        if (!$this->getJob()->hasFlexibleSchedule() || !$type) {
            $type = (int) $this->getJob()->getDefaultScheduleType();
        }

        return $type;
    }

    /**
     * @return int
     */
    public function getEffectiveScheduleValue() : int
    {
        $type = $this->getScheduleType();
        $value = $this->getScheduleValue();
        if (!$this->getJob()->hasFlexibleSchedule() || !$type) {
            $value = (int) $this->getJob()->getDefaultScheduleValue();
        }

        return $value;
    }
}
