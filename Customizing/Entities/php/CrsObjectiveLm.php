<?php



/**
 * CrsObjectiveLm
 */
class CrsObjectiveLm
{
    /**
     * @var int
     */
    private $lmAssId = '0';

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
     * @var string|null
     */
    private $type;

    /**
     * @var int|null
     */
    private $position = '0';


    /**
     * Get lmAssId.
     *
     * @return int
     */
    public function getLmAssId()
    {
        return $this->lmAssId;
    }

    /**
     * Set objectiveId.
     *
     * @param int $objectiveId
     *
     * @return CrsObjectiveLm
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
     * @return CrsObjectiveLm
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
     * @return CrsObjectiveLm
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
     * Set type.
     *
     * @param string|null $type
     *
     * @return CrsObjectiveLm
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set position.
     *
     * @param int|null $position
     *
     * @return CrsObjectiveLm
     */
    public function setPosition($position = null)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return int|null
     */
    public function getPosition()
    {
        return $this->position;
    }
}
