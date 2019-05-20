<?php



/**
 * LsoStates
 */
class LsoStates
{
    /**
     * @var int
     */
    private $lsoRefId;

    /**
     * @var int
     */
    private $usrId;

    /**
     * @var int|null
     */
    private $currentItem;

    /**
     * @var string|null
     */
    private $states;

    /**
     * @var string|null
     */
    private $firstAccess;

    /**
     * @var string|null
     */
    private $lastAccess;


    /**
     * Set lsoRefId.
     *
     * @param int $lsoRefId
     *
     * @return LsoStates
     */
    public function setLsoRefId($lsoRefId)
    {
        $this->lsoRefId = $lsoRefId;

        return $this;
    }

    /**
     * Get lsoRefId.
     *
     * @return int
     */
    public function getLsoRefId()
    {
        return $this->lsoRefId;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return LsoStates
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
     * Set currentItem.
     *
     * @param int|null $currentItem
     *
     * @return LsoStates
     */
    public function setCurrentItem($currentItem = null)
    {
        $this->currentItem = $currentItem;

        return $this;
    }

    /**
     * Get currentItem.
     *
     * @return int|null
     */
    public function getCurrentItem()
    {
        return $this->currentItem;
    }

    /**
     * Set states.
     *
     * @param string|null $states
     *
     * @return LsoStates
     */
    public function setStates($states = null)
    {
        $this->states = $states;

        return $this;
    }

    /**
     * Get states.
     *
     * @return string|null
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * Set firstAccess.
     *
     * @param string|null $firstAccess
     *
     * @return LsoStates
     */
    public function setFirstAccess($firstAccess = null)
    {
        $this->firstAccess = $firstAccess;

        return $this;
    }

    /**
     * Get firstAccess.
     *
     * @return string|null
     */
    public function getFirstAccess()
    {
        return $this->firstAccess;
    }

    /**
     * Set lastAccess.
     *
     * @param string|null $lastAccess
     *
     * @return LsoStates
     */
    public function setLastAccess($lastAccess = null)
    {
        $this->lastAccess = $lastAccess;

        return $this;
    }

    /**
     * Get lastAccess.
     *
     * @return string|null
     */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }
}
