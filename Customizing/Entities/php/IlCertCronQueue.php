<?php



/**
 * IlCertCronQueue
 */
class IlCertCronQueue
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string
     */
    private $adapterClass;

    /**
     * @var string
     */
    private $state;

    /**
     * @var int
     */
    private $startedTimestamp = '0';

    /**
     * @var int
     */
    private $templateId = '0';


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return IlCertCronQueue
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

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
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return IlCertCronQueue
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
     * Set adapterClass.
     *
     * @param string $adapterClass
     *
     * @return IlCertCronQueue
     */
    public function setAdapterClass($adapterClass)
    {
        $this->adapterClass = $adapterClass;

        return $this;
    }

    /**
     * Get adapterClass.
     *
     * @return string
     */
    public function getAdapterClass()
    {
        return $this->adapterClass;
    }

    /**
     * Set state.
     *
     * @param string $state
     *
     * @return IlCertCronQueue
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set startedTimestamp.
     *
     * @param int $startedTimestamp
     *
     * @return IlCertCronQueue
     */
    public function setStartedTimestamp($startedTimestamp)
    {
        $this->startedTimestamp = $startedTimestamp;

        return $this;
    }

    /**
     * Get startedTimestamp.
     *
     * @return int
     */
    public function getStartedTimestamp()
    {
        return $this->startedTimestamp;
    }

    /**
     * Set templateId.
     *
     * @param int $templateId
     *
     * @return IlCertCronQueue
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * Get templateId.
     *
     * @return int
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }
}
