<?php



/**
 * LocTstRun
 */
class LocTstRun
{
    /**
     * @var int
     */
    private $containerId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $testId = '0';

    /**
     * @var int
     */
    private $objectiveId = '0';

    /**
     * @var int|null
     */
    private $maxPoints = '0';

    /**
     * @var string|null
     */
    private $questions = '0';


    /**
     * Set containerId.
     *
     * @param int $containerId
     *
     * @return LocTstRun
     */
    public function setContainerId($containerId)
    {
        $this->containerId = $containerId;

        return $this;
    }

    /**
     * Get containerId.
     *
     * @return int
     */
    public function getContainerId()
    {
        return $this->containerId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return LocTstRun
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
     * Set testId.
     *
     * @param int $testId
     *
     * @return LocTstRun
     */
    public function setTestId($testId)
    {
        $this->testId = $testId;

        return $this;
    }

    /**
     * Get testId.
     *
     * @return int
     */
    public function getTestId()
    {
        return $this->testId;
    }

    /**
     * Set objectiveId.
     *
     * @param int $objectiveId
     *
     * @return LocTstRun
     */
    public function setObjectiveId($objectiveId)
    {
        $this->objectiveId = $objectiveId;

        return $this;
    }

    /**
     * Get objectiveId.
     *
     * @return int
     */
    public function getObjectiveId()
    {
        return $this->objectiveId;
    }

    /**
     * Set maxPoints.
     *
     * @param int|null $maxPoints
     *
     * @return LocTstRun
     */
    public function setMaxPoints($maxPoints = null)
    {
        $this->maxPoints = $maxPoints;

        return $this;
    }

    /**
     * Get maxPoints.
     *
     * @return int|null
     */
    public function getMaxPoints()
    {
        return $this->maxPoints;
    }

    /**
     * Set questions.
     *
     * @param string|null $questions
     *
     * @return LocTstRun
     */
    public function setQuestions($questions = null)
    {
        $this->questions = $questions;

        return $this;
    }

    /**
     * Get questions.
     *
     * @return string|null
     */
    public function getQuestions()
    {
        return $this->questions;
    }
}
