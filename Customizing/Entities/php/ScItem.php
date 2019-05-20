<?php



/**
 * ScItem
 */
class ScItem
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $importId;

    /**
     * @var string|null
     */
    private $identifierref;

    /**
     * @var string|null
     */
    private $isvisible;

    /**
     * @var string|null
     */
    private $parameters;

    /**
     * @var string|null
     */
    private $prereqType;

    /**
     * @var string|null
     */
    private $prerequisites;

    /**
     * @var string|null
     */
    private $maxtimeallowed;

    /**
     * @var string|null
     */
    private $timelimitaction;

    /**
     * @var string|null
     */
    private $datafromlms;

    /**
     * @var string|null
     */
    private $masteryscore;


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
     * Set importId.
     *
     * @param string|null $importId
     *
     * @return ScItem
     */
    public function setImportId($importId = null)
    {
        $this->importId = $importId;

        return $this;
    }

    /**
     * Get importId.
     *
     * @return string|null
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * Set identifierref.
     *
     * @param string|null $identifierref
     *
     * @return ScItem
     */
    public function setIdentifierref($identifierref = null)
    {
        $this->identifierref = $identifierref;

        return $this;
    }

    /**
     * Get identifierref.
     *
     * @return string|null
     */
    public function getIdentifierref()
    {
        return $this->identifierref;
    }

    /**
     * Set isvisible.
     *
     * @param string|null $isvisible
     *
     * @return ScItem
     */
    public function setIsvisible($isvisible = null)
    {
        $this->isvisible = $isvisible;

        return $this;
    }

    /**
     * Get isvisible.
     *
     * @return string|null
     */
    public function getIsvisible()
    {
        return $this->isvisible;
    }

    /**
     * Set parameters.
     *
     * @param string|null $parameters
     *
     * @return ScItem
     */
    public function setParameters($parameters = null)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get parameters.
     *
     * @return string|null
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Set prereqType.
     *
     * @param string|null $prereqType
     *
     * @return ScItem
     */
    public function setPrereqType($prereqType = null)
    {
        $this->prereqType = $prereqType;

        return $this;
    }

    /**
     * Get prereqType.
     *
     * @return string|null
     */
    public function getPrereqType()
    {
        return $this->prereqType;
    }

    /**
     * Set prerequisites.
     *
     * @param string|null $prerequisites
     *
     * @return ScItem
     */
    public function setPrerequisites($prerequisites = null)
    {
        $this->prerequisites = $prerequisites;

        return $this;
    }

    /**
     * Get prerequisites.
     *
     * @return string|null
     */
    public function getPrerequisites()
    {
        return $this->prerequisites;
    }

    /**
     * Set maxtimeallowed.
     *
     * @param string|null $maxtimeallowed
     *
     * @return ScItem
     */
    public function setMaxtimeallowed($maxtimeallowed = null)
    {
        $this->maxtimeallowed = $maxtimeallowed;

        return $this;
    }

    /**
     * Get maxtimeallowed.
     *
     * @return string|null
     */
    public function getMaxtimeallowed()
    {
        return $this->maxtimeallowed;
    }

    /**
     * Set timelimitaction.
     *
     * @param string|null $timelimitaction
     *
     * @return ScItem
     */
    public function setTimelimitaction($timelimitaction = null)
    {
        $this->timelimitaction = $timelimitaction;

        return $this;
    }

    /**
     * Get timelimitaction.
     *
     * @return string|null
     */
    public function getTimelimitaction()
    {
        return $this->timelimitaction;
    }

    /**
     * Set datafromlms.
     *
     * @param string|null $datafromlms
     *
     * @return ScItem
     */
    public function setDatafromlms($datafromlms = null)
    {
        $this->datafromlms = $datafromlms;

        return $this;
    }

    /**
     * Get datafromlms.
     *
     * @return string|null
     */
    public function getDatafromlms()
    {
        return $this->datafromlms;
    }

    /**
     * Set masteryscore.
     *
     * @param string|null $masteryscore
     *
     * @return ScItem
     */
    public function setMasteryscore($masteryscore = null)
    {
        $this->masteryscore = $masteryscore;

        return $this;
    }

    /**
     * Get masteryscore.
     *
     * @return string|null
     */
    public function getMasteryscore()
    {
        return $this->masteryscore;
    }
}
