<?php



/**
 * CpRule
 */
class CpRule
{
    /**
     * @var int
     */
    private $cpNodeId = '0';

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
    private $cType;


    /**
     * Get cpNodeId.
     *
     * @return int
     */
    public function getCpNodeId()
    {
        return $this->cpNodeId;
    }

    /**
     * Set action.
     *
     * @param string|null $action
     *
     * @return CpRule
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
     * @return CpRule
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
     * @return CpRule
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
     * @return CpRule
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
     * @return CpRule
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
     * Set cType.
     *
     * @param string|null $cType
     *
     * @return CpRule
     */
    public function setCType($cType = null)
    {
        $this->cType = $cType;

        return $this;
    }

    /**
     * Get cType.
     *
     * @return string|null
     */
    public function getCType()
    {
        return $this->cType;
    }
}
