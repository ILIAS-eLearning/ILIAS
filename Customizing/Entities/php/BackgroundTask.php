<?php



/**
 * BackgroundTask
 */
class BackgroundTask
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string|null
     */
    private $handler;

    /**
     * @var int
     */
    private $steps = '0';

    /**
     * @var int|null
     */
    private $cstep;

    /**
     * @var \DateTime|null
     */
    private $startDate;

    /**
     * @var string|null
     */
    private $status;

    /**
     * @var string|null
     */
    private $params;


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
     * @param int $userId
     *
     * @return BackgroundTask
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set handler.
     *
     * @param string|null $handler
     *
     * @return BackgroundTask
     */
    public function setHandler($handler = null)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Get handler.
     *
     * @return string|null
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set steps.
     *
     * @param int $steps
     *
     * @return BackgroundTask
     */
    public function setSteps($steps)
    {
        $this->steps = $steps;

        return $this;
    }

    /**
     * Get steps.
     *
     * @return int
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Set cstep.
     *
     * @param int|null $cstep
     *
     * @return BackgroundTask
     */
    public function setCstep($cstep = null)
    {
        $this->cstep = $cstep;

        return $this;
    }

    /**
     * Get cstep.
     *
     * @return int|null
     */
    public function getCstep()
    {
        return $this->cstep;
    }

    /**
     * Set startDate.
     *
     * @param \DateTime|null $startDate
     *
     * @return BackgroundTask
     */
    public function setStartDate($startDate = null)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set status.
     *
     * @param string|null $status
     *
     * @return BackgroundTask
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set params.
     *
     * @param string|null $params
     *
     * @return BackgroundTask
     */
    public function setParams($params = null)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get params.
     *
     * @return string|null
     */
    public function getParams()
    {
        return $this->params;
    }
}
