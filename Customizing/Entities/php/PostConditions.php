<?php



/**
 * PostConditions
 */
class PostConditions
{
    /**
     * @var int
     */
    private $refId;

    /**
     * @var int
     */
    private $value;

    /**
     * @var string
     */
    private $conditionOperator;


    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return PostConditions
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
     * Set value.
     *
     * @param int $value
     *
     * @return PostConditions
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set conditionOperator.
     *
     * @param string $conditionOperator
     *
     * @return PostConditions
     */
    public function setConditionOperator($conditionOperator)
    {
        $this->conditionOperator = $conditionOperator;

        return $this;
    }

    /**
     * Get conditionOperator.
     *
     * @return string
     */
    public function getConditionOperator()
    {
        return $this->conditionOperator;
    }
}
