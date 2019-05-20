<?php



/**
 * LocSettings
 */
class LocSettings
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var bool
     */
    private $type = '0';

    /**
     * @var int|null
     */
    private $itest;

    /**
     * @var int|null
     */
    private $qtest;

    /**
     * @var bool|null
     */
    private $qtVisAll = '1';

    /**
     * @var bool|null
     */
    private $qtVisObj = '0';

    /**
     * @var bool|null
     */
    private $resetResults = '0';

    /**
     * @var bool|null
     */
    private $itType = '5';

    /**
     * @var bool|null
     */
    private $qtType = '1';

    /**
     * @var bool|null
     */
    private $itStart = '1';

    /**
     * @var bool|null
     */
    private $qtStart = '1';

    /**
     * @var bool|null
     */
    private $passedObjMode = '1';


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
     * @param bool $type
     *
     * @return LocSettings
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return bool
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set itest.
     *
     * @param int|null $itest
     *
     * @return LocSettings
     */
    public function setItest($itest = null)
    {
        $this->itest = $itest;

        return $this;
    }

    /**
     * Get itest.
     *
     * @return int|null
     */
    public function getItest()
    {
        return $this->itest;
    }

    /**
     * Set qtest.
     *
     * @param int|null $qtest
     *
     * @return LocSettings
     */
    public function setQtest($qtest = null)
    {
        $this->qtest = $qtest;

        return $this;
    }

    /**
     * Get qtest.
     *
     * @return int|null
     */
    public function getQtest()
    {
        return $this->qtest;
    }

    /**
     * Set qtVisAll.
     *
     * @param bool|null $qtVisAll
     *
     * @return LocSettings
     */
    public function setQtVisAll($qtVisAll = null)
    {
        $this->qtVisAll = $qtVisAll;

        return $this;
    }

    /**
     * Get qtVisAll.
     *
     * @return bool|null
     */
    public function getQtVisAll()
    {
        return $this->qtVisAll;
    }

    /**
     * Set qtVisObj.
     *
     * @param bool|null $qtVisObj
     *
     * @return LocSettings
     */
    public function setQtVisObj($qtVisObj = null)
    {
        $this->qtVisObj = $qtVisObj;

        return $this;
    }

    /**
     * Get qtVisObj.
     *
     * @return bool|null
     */
    public function getQtVisObj()
    {
        return $this->qtVisObj;
    }

    /**
     * Set resetResults.
     *
     * @param bool|null $resetResults
     *
     * @return LocSettings
     */
    public function setResetResults($resetResults = null)
    {
        $this->resetResults = $resetResults;

        return $this;
    }

    /**
     * Get resetResults.
     *
     * @return bool|null
     */
    public function getResetResults()
    {
        return $this->resetResults;
    }

    /**
     * Set itType.
     *
     * @param bool|null $itType
     *
     * @return LocSettings
     */
    public function setItType($itType = null)
    {
        $this->itType = $itType;

        return $this;
    }

    /**
     * Get itType.
     *
     * @return bool|null
     */
    public function getItType()
    {
        return $this->itType;
    }

    /**
     * Set qtType.
     *
     * @param bool|null $qtType
     *
     * @return LocSettings
     */
    public function setQtType($qtType = null)
    {
        $this->qtType = $qtType;

        return $this;
    }

    /**
     * Get qtType.
     *
     * @return bool|null
     */
    public function getQtType()
    {
        return $this->qtType;
    }

    /**
     * Set itStart.
     *
     * @param bool|null $itStart
     *
     * @return LocSettings
     */
    public function setItStart($itStart = null)
    {
        $this->itStart = $itStart;

        return $this;
    }

    /**
     * Get itStart.
     *
     * @return bool|null
     */
    public function getItStart()
    {
        return $this->itStart;
    }

    /**
     * Set qtStart.
     *
     * @param bool|null $qtStart
     *
     * @return LocSettings
     */
    public function setQtStart($qtStart = null)
    {
        $this->qtStart = $qtStart;

        return $this;
    }

    /**
     * Get qtStart.
     *
     * @return bool|null
     */
    public function getQtStart()
    {
        return $this->qtStart;
    }

    /**
     * Set passedObjMode.
     *
     * @param bool|null $passedObjMode
     *
     * @return LocSettings
     */
    public function setPassedObjMode($passedObjMode = null)
    {
        $this->passedObjMode = $passedObjMode;

        return $this;
    }

    /**
     * Get passedObjMode.
     *
     * @return bool|null
     */
    public function getPassedObjMode()
    {
        return $this->passedObjMode;
    }
}
