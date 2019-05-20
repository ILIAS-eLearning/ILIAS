<?php



/**
 * EcsCourseAssignments
 */
class EcsCourseAssignments
{
    /**
     * @var int
     */
    private $id = '0';

    /**
     * @var int
     */
    private $sid = '0';

    /**
     * @var int
     */
    private $mid = '0';

    /**
     * @var int
     */
    private $cmsId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $usrId;

    /**
     * @var bool
     */
    private $status = '0';

    /**
     * @var int|null
     */
    private $cmsSubId = '0';


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
     * Set sid.
     *
     * @param int $sid
     *
     * @return EcsCourseAssignments
     */
    public function setSid($sid)
    {
        $this->sid = $sid;

        return $this;
    }

    /**
     * Get sid.
     *
     * @return int
     */
    public function getSid()
    {
        return $this->sid;
    }

    /**
     * Set mid.
     *
     * @param int $mid
     *
     * @return EcsCourseAssignments
     */
    public function setMid($mid)
    {
        $this->mid = $mid;

        return $this;
    }

    /**
     * Get mid.
     *
     * @return int
     */
    public function getMid()
    {
        return $this->mid;
    }

    /**
     * Set cmsId.
     *
     * @param int $cmsId
     *
     * @return EcsCourseAssignments
     */
    public function setCmsId($cmsId)
    {
        $this->cmsId = $cmsId;

        return $this;
    }

    /**
     * Get cmsId.
     *
     * @return int
     */
    public function getCmsId()
    {
        return $this->cmsId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return EcsCourseAssignments
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
     * @param string|null $usrId
     *
     * @return EcsCourseAssignments
     */
    public function setUsrId($usrId = null)
    {
        $this->usrId = $usrId;

        return $this;
    }

    /**
     * Get usrId.
     *
     * @return string|null
     */
    public function getUsrId()
    {
        return $this->usrId;
    }

    /**
     * Set status.
     *
     * @param bool $status
     *
     * @return EcsCourseAssignments
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set cmsSubId.
     *
     * @param int|null $cmsSubId
     *
     * @return EcsCourseAssignments
     */
    public function setCmsSubId($cmsSubId = null)
    {
        $this->cmsSubId = $cmsSubId;

        return $this;
    }

    /**
     * Get cmsSubId.
     *
     * @return int|null
     */
    public function getCmsSubId()
    {
        return $this->cmsSubId;
    }
}
