<?php



/**
 * CronJob
 */
class CronJob
{
    /**
     * @var string
     */
    private $jobId = '';

    /**
     * @var string|null
     */
    private $component;

    /**
     * @var bool|null
     */
    private $scheduleType;

    /**
     * @var int|null
     */
    private $scheduleValue;

    /**
     * @var bool|null
     */
    private $jobStatus;

    /**
     * @var int|null
     */
    private $jobStatusUserId;

    /**
     * @var bool|null
     */
    private $jobStatusType;

    /**
     * @var int|null
     */
    private $jobStatusTs;

    /**
     * @var bool|null
     */
    private $jobResultStatus;

    /**
     * @var int|null
     */
    private $jobResultUserId;

    /**
     * @var string|null
     */
    private $jobResultCode;

    /**
     * @var string|null
     */
    private $jobResultMessage;

    /**
     * @var bool|null
     */
    private $jobResultType;

    /**
     * @var int|null
     */
    private $jobResultTs;

    /**
     * @var string|null
     */
    private $class;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var int|null
     */
    private $runningTs;

    /**
     * @var int|null
     */
    private $jobResultDur;

    /**
     * @var int|null
     */
    private $aliveTs;


    /**
     * Get jobId.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * Set component.
     *
     * @param string|null $component
     *
     * @return CronJob
     */
    public function setComponent($component = null)
    {
        $this->component = $component;

        return $this;
    }

    /**
     * Get component.
     *
     * @return string|null
     */
    public function getComponent()
    {
        return $this->component;
    }

    /**
     * Set scheduleType.
     *
     * @param bool|null $scheduleType
     *
     * @return CronJob
     */
    public function setScheduleType($scheduleType = null)
    {
        $this->scheduleType = $scheduleType;

        return $this;
    }

    /**
     * Get scheduleType.
     *
     * @return bool|null
     */
    public function getScheduleType()
    {
        return $this->scheduleType;
    }

    /**
     * Set scheduleValue.
     *
     * @param int|null $scheduleValue
     *
     * @return CronJob
     */
    public function setScheduleValue($scheduleValue = null)
    {
        $this->scheduleValue = $scheduleValue;

        return $this;
    }

    /**
     * Get scheduleValue.
     *
     * @return int|null
     */
    public function getScheduleValue()
    {
        return $this->scheduleValue;
    }

    /**
     * Set jobStatus.
     *
     * @param bool|null $jobStatus
     *
     * @return CronJob
     */
    public function setJobStatus($jobStatus = null)
    {
        $this->jobStatus = $jobStatus;

        return $this;
    }

    /**
     * Get jobStatus.
     *
     * @return bool|null
     */
    public function getJobStatus()
    {
        return $this->jobStatus;
    }

    /**
     * Set jobStatusUserId.
     *
     * @param int|null $jobStatusUserId
     *
     * @return CronJob
     */
    public function setJobStatusUserId($jobStatusUserId = null)
    {
        $this->jobStatusUserId = $jobStatusUserId;

        return $this;
    }

    /**
     * Get jobStatusUserId.
     *
     * @return int|null
     */
    public function getJobStatusUserId()
    {
        return $this->jobStatusUserId;
    }

    /**
     * Set jobStatusType.
     *
     * @param bool|null $jobStatusType
     *
     * @return CronJob
     */
    public function setJobStatusType($jobStatusType = null)
    {
        $this->jobStatusType = $jobStatusType;

        return $this;
    }

    /**
     * Get jobStatusType.
     *
     * @return bool|null
     */
    public function getJobStatusType()
    {
        return $this->jobStatusType;
    }

    /**
     * Set jobStatusTs.
     *
     * @param int|null $jobStatusTs
     *
     * @return CronJob
     */
    public function setJobStatusTs($jobStatusTs = null)
    {
        $this->jobStatusTs = $jobStatusTs;

        return $this;
    }

    /**
     * Get jobStatusTs.
     *
     * @return int|null
     */
    public function getJobStatusTs()
    {
        return $this->jobStatusTs;
    }

    /**
     * Set jobResultStatus.
     *
     * @param bool|null $jobResultStatus
     *
     * @return CronJob
     */
    public function setJobResultStatus($jobResultStatus = null)
    {
        $this->jobResultStatus = $jobResultStatus;

        return $this;
    }

    /**
     * Get jobResultStatus.
     *
     * @return bool|null
     */
    public function getJobResultStatus()
    {
        return $this->jobResultStatus;
    }

    /**
     * Set jobResultUserId.
     *
     * @param int|null $jobResultUserId
     *
     * @return CronJob
     */
    public function setJobResultUserId($jobResultUserId = null)
    {
        $this->jobResultUserId = $jobResultUserId;

        return $this;
    }

    /**
     * Get jobResultUserId.
     *
     * @return int|null
     */
    public function getJobResultUserId()
    {
        return $this->jobResultUserId;
    }

    /**
     * Set jobResultCode.
     *
     * @param string|null $jobResultCode
     *
     * @return CronJob
     */
    public function setJobResultCode($jobResultCode = null)
    {
        $this->jobResultCode = $jobResultCode;

        return $this;
    }

    /**
     * Get jobResultCode.
     *
     * @return string|null
     */
    public function getJobResultCode()
    {
        return $this->jobResultCode;
    }

    /**
     * Set jobResultMessage.
     *
     * @param string|null $jobResultMessage
     *
     * @return CronJob
     */
    public function setJobResultMessage($jobResultMessage = null)
    {
        $this->jobResultMessage = $jobResultMessage;

        return $this;
    }

    /**
     * Get jobResultMessage.
     *
     * @return string|null
     */
    public function getJobResultMessage()
    {
        return $this->jobResultMessage;
    }

    /**
     * Set jobResultType.
     *
     * @param bool|null $jobResultType
     *
     * @return CronJob
     */
    public function setJobResultType($jobResultType = null)
    {
        $this->jobResultType = $jobResultType;

        return $this;
    }

    /**
     * Get jobResultType.
     *
     * @return bool|null
     */
    public function getJobResultType()
    {
        return $this->jobResultType;
    }

    /**
     * Set jobResultTs.
     *
     * @param int|null $jobResultTs
     *
     * @return CronJob
     */
    public function setJobResultTs($jobResultTs = null)
    {
        $this->jobResultTs = $jobResultTs;

        return $this;
    }

    /**
     * Get jobResultTs.
     *
     * @return int|null
     */
    public function getJobResultTs()
    {
        return $this->jobResultTs;
    }

    /**
     * Set class.
     *
     * @param string|null $class
     *
     * @return CronJob
     */
    public function setClass($class = null)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class.
     *
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set path.
     *
     * @param string|null $path
     *
     * @return CronJob
     */
    public function setPath($path = null)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path.
     *
     * @return string|null
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set runningTs.
     *
     * @param int|null $runningTs
     *
     * @return CronJob
     */
    public function setRunningTs($runningTs = null)
    {
        $this->runningTs = $runningTs;

        return $this;
    }

    /**
     * Get runningTs.
     *
     * @return int|null
     */
    public function getRunningTs()
    {
        return $this->runningTs;
    }

    /**
     * Set jobResultDur.
     *
     * @param int|null $jobResultDur
     *
     * @return CronJob
     */
    public function setJobResultDur($jobResultDur = null)
    {
        $this->jobResultDur = $jobResultDur;

        return $this;
    }

    /**
     * Get jobResultDur.
     *
     * @return int|null
     */
    public function getJobResultDur()
    {
        return $this->jobResultDur;
    }

    /**
     * Set aliveTs.
     *
     * @param int|null $aliveTs
     *
     * @return CronJob
     */
    public function setAliveTs($aliveTs = null)
    {
        $this->aliveTs = $aliveTs;

        return $this;
    }

    /**
     * Get aliveTs.
     *
     * @return int|null
     */
    public function getAliveTs()
    {
        return $this->aliveTs;
    }
}
