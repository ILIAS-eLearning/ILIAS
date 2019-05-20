<?php



/**
 * AssLog
 */
class AssLog
{
    /**
     * @var int
     */
    private $assLogId = '0';

    /**
     * @var int
     */
    private $userFi = '0';

    /**
     * @var int
     */
    private $objFi = '0';

    /**
     * @var string|null
     */
    private $logtext;

    /**
     * @var int|null
     */
    private $questionFi;

    /**
     * @var int|null
     */
    private $originalFi;

    /**
     * @var int|null
     */
    private $refId;

    /**
     * @var string|null
     */
    private $testOnly = '0';

    /**
     * @var int
     */
    private $tstamp = '0';


    /**
     * Get assLogId.
     *
     * @return int
     */
    public function getAssLogId()
    {
        return $this->assLogId;
    }

    /**
     * Set userFi.
     *
     * @param int $userFi
     *
     * @return AssLog
     */
    public function setUserFi($userFi)
    {
        $this->userFi = $userFi;

        return $this;
    }

    /**
     * Get userFi.
     *
     * @return int
     */
    public function getUserFi()
    {
        return $this->userFi;
    }

    /**
     * Set objFi.
     *
     * @param int $objFi
     *
     * @return AssLog
     */
    public function setObjFi($objFi)
    {
        $this->objFi = $objFi;

        return $this;
    }

    /**
     * Get objFi.
     *
     * @return int
     */
    public function getObjFi()
    {
        return $this->objFi;
    }

    /**
     * Set logtext.
     *
     * @param string|null $logtext
     *
     * @return AssLog
     */
    public function setLogtext($logtext = null)
    {
        $this->logtext = $logtext;

        return $this;
    }

    /**
     * Get logtext.
     *
     * @return string|null
     */
    public function getLogtext()
    {
        return $this->logtext;
    }

    /**
     * Set questionFi.
     *
     * @param int|null $questionFi
     *
     * @return AssLog
     */
    public function setQuestionFi($questionFi = null)
    {
        $this->questionFi = $questionFi;

        return $this;
    }

    /**
     * Get questionFi.
     *
     * @return int|null
     */
    public function getQuestionFi()
    {
        return $this->questionFi;
    }

    /**
     * Set originalFi.
     *
     * @param int|null $originalFi
     *
     * @return AssLog
     */
    public function setOriginalFi($originalFi = null)
    {
        $this->originalFi = $originalFi;

        return $this;
    }

    /**
     * Get originalFi.
     *
     * @return int|null
     */
    public function getOriginalFi()
    {
        return $this->originalFi;
    }

    /**
     * Set refId.
     *
     * @param int|null $refId
     *
     * @return AssLog
     */
    public function setRefId($refId = null)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int|null
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set testOnly.
     *
     * @param string|null $testOnly
     *
     * @return AssLog
     */
    public function setTestOnly($testOnly = null)
    {
        $this->testOnly = $testOnly;

        return $this;
    }

    /**
     * Get testOnly.
     *
     * @return string|null
     */
    public function getTestOnly()
    {
        return $this->testOnly;
    }

    /**
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return AssLog
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }
}
