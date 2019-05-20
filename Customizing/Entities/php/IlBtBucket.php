<?php



/**
 * IlBtBucket
 */
class IlBtBucket
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int|null
     */
    private $userId;

    /**
     * @var int|null
     */
    private $rootTaskId;

    /**
     * @var int|null
     */
    private $currentTaskId;

    /**
     * @var int|null
     */
    private $state;

    /**
     * @var int|null
     */
    private $totalNumberOfTasks;

    /**
     * @var int|null
     */
    private $percentage;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var int|null
     */
    private $lastHeartbeat;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId.
     *
     * @param int|null $userId
     *
     * @return IlBtBucket
     */
    public function setUserId($userId = null)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int|null
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set rootTaskId.
     *
     * @param int|null $rootTaskId
     *
     * @return IlBtBucket
     */
    public function setRootTaskId($rootTaskId = null)
    {
        $this->rootTaskId = $rootTaskId;

        return $this;
    }

    /**
     * Get rootTaskId.
     *
     * @return int|null
     */
    public function getRootTaskId()
    {
        return $this->rootTaskId;
    }

    /**
     * Set currentTaskId.
     *
     * @param int|null $currentTaskId
     *
     * @return IlBtBucket
     */
    public function setCurrentTaskId($currentTaskId = null)
    {
        $this->currentTaskId = $currentTaskId;

        return $this;
    }

    /**
     * Get currentTaskId.
     *
     * @return int|null
     */
    public function getCurrentTaskId()
    {
        return $this->currentTaskId;
    }

    /**
     * Set state.
     *
     * @param int|null $state
     *
     * @return IlBtBucket
     */
    public function setState($state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return int|null
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set totalNumberOfTasks.
     *
     * @param int|null $totalNumberOfTasks
     *
     * @return IlBtBucket
     */
    public function setTotalNumberOfTasks($totalNumberOfTasks = null)
    {
        $this->totalNumberOfTasks = $totalNumberOfTasks;

        return $this;
    }

    /**
     * Get totalNumberOfTasks.
     *
     * @return int|null
     */
    public function getTotalNumberOfTasks()
    {
        return $this->totalNumberOfTasks;
    }

    /**
     * Set percentage.
     *
     * @param int|null $percentage
     *
     * @return IlBtBucket
     */
    public function setPercentage($percentage = null)
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * Get percentage.
     *
     * @return int|null
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * Set title.
     *
     * @param string|null $title
     *
     * @return IlBtBucket
     */
    public function setTitle($title = null)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string|null $description
     *
     * @return IlBtBucket
     */
    public function setDescription($description = null)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set lastHeartbeat.
     *
     * @param int|null $lastHeartbeat
     *
     * @return IlBtBucket
     */
    public function setLastHeartbeat($lastHeartbeat = null)
    {
        $this->lastHeartbeat = $lastHeartbeat;

        return $this;
    }

    /**
     * Get lastHeartbeat.
     *
     * @return int|null
     */
    public function getLastHeartbeat()
    {
        return $this->lastHeartbeat;
    }
}
