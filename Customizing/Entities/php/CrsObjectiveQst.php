<?php



/**
 * CrsObjectiveQst
 */
class CrsObjectiveQst
{
    /**
     * @var int
     */
    private $qstAssId = '0';

    /**
     * @var int
     */
    private $objectiveId = '0';

    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $questionId = '0';


    /**
     * Get qstAssId.
     *
     * @return int
     */
    public function getQstAssId()
    {
        return $this->qstAssId;
    }

    /**
     * Set objectiveId.
     *
     * @param int $objectiveId
     *
     * @return CrsObjectiveQst
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
     * Set refId.
     *
     * @param int $refId
     *
     * @return CrsObjectiveQst
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return CrsObjectiveQst
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set questionId.
     *
     * @param int $questionId
     *
     * @return CrsObjectiveQst
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;

        return $this;
    }

    /**
     * Get questionId.
     *
     * @return int
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }
}
