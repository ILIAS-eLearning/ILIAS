<?php



/**
 * SahsSc13SeqRule
 */
class SahsSc13SeqRule
{
    /**
     * @var int
     */
    private $seqnodeid = '0';

    /**
     * @var string|null
     */
    private $action;

    /**
     * @var string|null
     */
    private $childactivityset;

    /**
     * @var string|null
     */
    private $conditioncombination;

    /**
     * @var int|null
     */
    private $minimumcount;

    /**
     * @var string|null
     */
    private $minimumpercent;

    /**
     * @var string|null
     */
    private $type;


    /**
     * Get seqnodeid.
     *
     * @return int
     */
    public function getSeqnodeid()
    {
        return $this->seqnodeid;
    }

    /**
     * Set action.
     *
     * @param string|null $action
     *
     * @return SahsSc13SeqRule
     */
    public function setAction($action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return string|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set childactivityset.
     *
     * @param string|null $childactivityset
     *
     * @return SahsSc13SeqRule
     */
    public function setChildactivityset($childactivityset = null)
    {
        $this->childactivityset = $childactivityset;

        return $this;
    }

    /**
     * Get childactivityset.
     *
     * @return string|null
     */
    public function getChildactivityset()
    {
        return $this->childactivityset;
    }

    /**
     * Set conditioncombination.
     *
     * @param string|null $conditioncombination
     *
     * @return SahsSc13SeqRule
     */
    public function setConditioncombination($conditioncombination = null)
    {
        $this->conditioncombination = $conditioncombination;

        return $this;
    }

    /**
     * Get conditioncombination.
     *
     * @return string|null
     */
    public function getConditioncombination()
    {
        return $this->conditioncombination;
    }

    /**
     * Set minimumcount.
     *
     * @param int|null $minimumcount
     *
     * @return SahsSc13SeqRule
     */
    public function setMinimumcount($minimumcount = null)
    {
        $this->minimumcount = $minimumcount;

        return $this;
    }

    /**
     * Get minimumcount.
     *
     * @return int|null
     */
    public function getMinimumcount()
    {
        return $this->minimumcount;
    }

    /**
     * Set minimumpercent.
     *
     * @param string|null $minimumpercent
     *
     * @return SahsSc13SeqRule
     */
    public function setMinimumpercent($minimumpercent = null)
    {
        $this->minimumpercent = $minimumpercent;

        return $this;
    }

    /**
     * Get minimumpercent.
     *
     * @return string|null
     */
    public function getMinimumpercent()
    {
        return $this->minimumpercent;
    }

    /**
     * Set type.
     *
     * @param string|null $type
     *
     * @return SahsSc13SeqRule
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
}
