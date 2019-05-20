<?php



/**
 * CrsTimingsPlaned
 */
class CrsTimingsPlaned
{
    /**
     * @var int
     */
    private $itemId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $planedStart = '0';

    /**
     * @var int
     */
    private $planedEnd = '0';


    /**
     * Set itemId.
     *
     * @param int $itemId
     *
     * @return CrsTimingsPlaned
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * Get itemId.
     *
     * @return int
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return CrsTimingsPlaned
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return int
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set planedStart.
     *
     * @param int $planedStart
     *
     * @return CrsTimingsPlaned
     */
    public function setPlanedStart($planedStart)
    {
        $this->planedStart = $planedStart;

        return $this;
    }

    /**
     * Get planedStart.
     *
     * @return int
     */
    public function getPlanedStart()
    {
        return $this->planedStart;
    }

    /**
     * Set planedEnd.
     *
     * @param int $planedEnd
     *
     * @return CrsTimingsPlaned
     */
    public function setPlanedEnd($planedEnd)
    {
        $this->planedEnd = $planedEnd;

        return $this;
    }

    /**
     * Get planedEnd.
     *
     * @return int
     */
    public function getPlanedEnd()
    {
        return $this->planedEnd;
    }
}
