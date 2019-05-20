<?php



/**
 * SvyConstraint
 */
class SvyConstraint
{
    /**
     * @var int
     */
    private $constraintId = '0';

    /**
     * @var int
     */
    private $questionFi = '0';

    /**
     * @var int
     */
    private $relationFi = '0';

    /**
     * @var float
     */
    private $value = '0';

    /**
     * @var int
     */
    private $conjunction = '0';


    /**
     * Get constraintId.
     *
     * @return int
     */
    public function getConstraintId()
    {
        return $this->constraintId;
    }

    /**
     * Set questionFi.
     *
     * @param int $questionFi
     *
     * @return SvyConstraint
     */
    public function setQuestionFi($questionFi)
    {
        $this->questionFi = $questionFi;

        return $this;
    }

    /**
     * Get questionFi.
     *
     * @return int
     */
    public function getQuestionFi()
    {
        return $this->questionFi;
    }

    /**
     * Set relationFi.
     *
     * @param int $relationFi
     *
     * @return SvyConstraint
     */
    public function setRelationFi($relationFi)
    {
        $this->relationFi = $relationFi;

        return $this;
    }

    /**
     * Get relationFi.
     *
     * @return int
     */
    public function getRelationFi()
    {
        return $this->relationFi;
    }

    /**
     * Set value.
     *
     * @param float $value
     *
     * @return SvyConstraint
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set conjunction.
     *
     * @param int $conjunction
     *
     * @return SvyConstraint
     */
    public function setConjunction($conjunction)
    {
        $this->conjunction = $conjunction;

        return $this;
    }

    /**
     * Get conjunction.
     *
     * @return int
     */
    public function getConjunction()
    {
        return $this->conjunction;
    }
}
