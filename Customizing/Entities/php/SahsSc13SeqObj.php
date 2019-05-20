<?php



/**
 * SahsSc13SeqObj
 */
class SahsSc13SeqObj
{
    /**
     * @var int
     */
    private $seqnodeid = '0';

    /**
     * @var string|null
     */
    private $minnormalizedmeasure;

    /**
     * @var string|null
     */
    private $objectiveid;

    /**
     * @var bool|null
     */
    private $primaryObj;

    /**
     * @var bool|null
     */
    private $satisfiedbymeasure;

    /**
     * @var string|null
     */
    private $importObjectiveId;


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
     * Set minnormalizedmeasure.
     *
     * @param string|null $minnormalizedmeasure
     *
     * @return SahsSc13SeqObj
     */
    public function setMinnormalizedmeasure($minnormalizedmeasure = null)
    {
        $this->minnormalizedmeasure = $minnormalizedmeasure;

        return $this;
    }

    /**
     * Get minnormalizedmeasure.
     *
     * @return string|null
     */
    public function getMinnormalizedmeasure()
    {
        return $this->minnormalizedmeasure;
    }

    /**
     * Set objectiveid.
     *
     * @param string|null $objectiveid
     *
     * @return SahsSc13SeqObj
     */
    public function setObjectiveid($objectiveid = null)
    {
        $this->objectiveid = $objectiveid;

        return $this;
    }

    /**
     * Get objectiveid.
     *
     * @return string|null
     */
    public function getObjectiveid()
    {
        return $this->objectiveid;
    }

    /**
     * Set primaryObj.
     *
     * @param bool|null $primaryObj
     *
     * @return SahsSc13SeqObj
     */
    public function setPrimaryObj($primaryObj = null)
    {
        $this->primaryObj = $primaryObj;

        return $this;
    }

    /**
     * Get primaryObj.
     *
     * @return bool|null
     */
    public function getPrimaryObj()
    {
        return $this->primaryObj;
    }

    /**
     * Set satisfiedbymeasure.
     *
     * @param bool|null $satisfiedbymeasure
     *
     * @return SahsSc13SeqObj
     */
    public function setSatisfiedbymeasure($satisfiedbymeasure = null)
    {
        $this->satisfiedbymeasure = $satisfiedbymeasure;

        return $this;
    }

    /**
     * Get satisfiedbymeasure.
     *
     * @return bool|null
     */
    public function getSatisfiedbymeasure()
    {
        return $this->satisfiedbymeasure;
    }

    /**
     * Set importObjectiveId.
     *
     * @param string|null $importObjectiveId
     *
     * @return SahsSc13SeqObj
     */
    public function setImportObjectiveId($importObjectiveId = null)
    {
        $this->importObjectiveId = $importObjectiveId;

        return $this;
    }

    /**
     * Get importObjectiveId.
     *
     * @return string|null
     */
    public function getImportObjectiveId()
    {
        return $this->importObjectiveId;
    }
}
