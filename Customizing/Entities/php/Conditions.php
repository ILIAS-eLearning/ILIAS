<?php



/**
 * Conditions
 */
class Conditions
{
    /**
     * @var int
     */
    private $conditionId = '0';

    /**
     * @var int
     */
    private $targetRefId = '0';

    /**
     * @var int
     */
    private $targetObjId = '0';

    /**
     * @var string|null
     */
    private $targetType;

    /**
     * @var int
     */
    private $triggerRefId = '0';

    /**
     * @var int
     */
    private $triggerObjId = '0';

    /**
     * @var string|null
     */
    private $triggerType;

    /**
     * @var string|null
     */
    private $operator;

    /**
     * @var string|null
     */
    private $value;

    /**
     * @var bool
     */
    private $refHandling = '1';

    /**
     * @var bool
     */
    private $obligatory = '1';

    /**
     * @var bool
     */
    private $numObligatory = '0';

    /**
     * @var bool|null
     */
    private $hiddenStatus = '0';


    /**
     * Get conditionId.
     *
     * @return int
     */
    public function getConditionId()
    {
        return $this->conditionId;
    }

    /**
     * Set targetRefId.
     *
     * @param int $targetRefId
     *
     * @return Conditions
     */
    public function setTargetRefId($targetRefId)
    {
        $this->targetRefId = $targetRefId;

        return $this;
    }

    /**
     * Get targetRefId.
     *
     * @return int
     */
    public function getTargetRefId()
    {
        return $this->targetRefId;
    }

    /**
     * Set targetObjId.
     *
     * @param int $targetObjId
     *
     * @return Conditions
     */
    public function setTargetObjId($targetObjId)
    {
        $this->targetObjId = $targetObjId;

        return $this;
    }

    /**
     * Get targetObjId.
     *
     * @return int
     */
    public function getTargetObjId()
    {
        return $this->targetObjId;
    }

    /**
     * Set targetType.
     *
     * @param string|null $targetType
     *
     * @return Conditions
     */
    public function setTargetType($targetType = null)
    {
        $this->targetType = $targetType;

        return $this;
    }

    /**
     * Get targetType.
     *
     * @return string|null
     */
    public function getTargetType()
    {
        return $this->targetType;
    }

    /**
     * Set triggerRefId.
     *
     * @param int $triggerRefId
     *
     * @return Conditions
     */
    public function setTriggerRefId($triggerRefId)
    {
        $this->triggerRefId = $triggerRefId;

        return $this;
    }

    /**
     * Get triggerRefId.
     *
     * @return int
     */
    public function getTriggerRefId()
    {
        return $this->triggerRefId;
    }

    /**
     * Set triggerObjId.
     *
     * @param int $triggerObjId
     *
     * @return Conditions
     */
    public function setTriggerObjId($triggerObjId)
    {
        $this->triggerObjId = $triggerObjId;

        return $this;
    }

    /**
     * Get triggerObjId.
     *
     * @return int
     */
    public function getTriggerObjId()
    {
        return $this->triggerObjId;
    }

    /**
     * Set triggerType.
     *
     * @param string|null $triggerType
     *
     * @return Conditions
     */
    public function setTriggerType($triggerType = null)
    {
        $this->triggerType = $triggerType;

        return $this;
    }

    /**
     * Get triggerType.
     *
     * @return string|null
     */
    public function getTriggerType()
    {
        return $this->triggerType;
    }

    /**
     * Set operator.
     *
     * @param string|null $operator
     *
     * @return Conditions
     */
    public function setOperator($operator = null)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Get operator.
     *
     * @return string|null
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Set value.
     *
     * @param string|null $value
     *
     * @return Conditions
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set refHandling.
     *
     * @param bool $refHandling
     *
     * @return Conditions
     */
    public function setRefHandling($refHandling)
    {
        $this->refHandling = $refHandling;

        return $this;
    }

    /**
     * Get refHandling.
     *
     * @return bool
     */
    public function getRefHandling()
    {
        return $this->refHandling;
    }

    /**
     * Set obligatory.
     *
     * @param bool $obligatory
     *
     * @return Conditions
     */
    public function setObligatory($obligatory)
    {
        $this->obligatory = $obligatory;

        return $this;
    }

    /**
     * Get obligatory.
     *
     * @return bool
     */
    public function getObligatory()
    {
        return $this->obligatory;
    }

    /**
     * Set numObligatory.
     *
     * @param bool $numObligatory
     *
     * @return Conditions
     */
    public function setNumObligatory($numObligatory)
    {
        $this->numObligatory = $numObligatory;

        return $this;
    }

    /**
     * Get numObligatory.
     *
     * @return bool
     */
    public function getNumObligatory()
    {
        return $this->numObligatory;
    }

    /**
     * Set hiddenStatus.
     *
     * @param bool|null $hiddenStatus
     *
     * @return Conditions
     */
    public function setHiddenStatus($hiddenStatus = null)
    {
        $this->hiddenStatus = $hiddenStatus;

        return $this;
    }

    /**
     * Get hiddenStatus.
     *
     * @return bool|null
     */
    public function getHiddenStatus()
    {
        return $this->hiddenStatus;
    }
}
