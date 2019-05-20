<?php



/**
 * IlSubscribers
 */
class IlSubscribers
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var string|null
     */
    private $subject;

    /**
     * @var int
     */
    private $subTime = '0';


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return IlSubscribers
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
     * Set objId.
     *
     * @param int $objId
     *
     * @return IlSubscribers
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
     * Set subject.
     *
     * @param string|null $subject
     *
     * @return IlSubscribers
     */
    public function setSubject($subject = null)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     *
     * @return string|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set subTime.
     *
     * @param int $subTime
     *
     * @return IlSubscribers
     */
    public function setSubTime($subTime)
    {
        $this->subTime = $subTime;

        return $this;
    }

    /**
     * Get subTime.
     *
     * @return int
     */
    public function getSubTime()
    {
        return $this->subTime;
    }
}
