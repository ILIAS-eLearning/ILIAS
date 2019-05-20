<?php



/**
 * ExcUsrTutor
 */
class ExcUsrTutor
{
    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $tutorId = '0';

    /**
     * @var int
     */
    private $assId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var \DateTime|null
     */
    private $downloadTime;


    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return ExcUsrTutor
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
     * Set tutorId.
     *
     * @param int $tutorId
     *
     * @return ExcUsrTutor
     */
    public function setTutorId($tutorId)
    {
        $this->tutorId = $tutorId;

        return $this;
    }

    /**
     * Get tutorId.
     *
     * @return int
     */
    public function getTutorId()
    {
        return $this->tutorId;
    }

    /**
     * Set assId.
     *
     * @param int $assId
     *
     * @return ExcUsrTutor
     */
    public function setAssId($assId)
    {
        $this->assId = $assId;

        return $this;
    }

    /**
     * Get assId.
     *
     * @return int
     */
    public function getAssId()
    {
        return $this->assId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ExcUsrTutor
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
     * Set downloadTime.
     *
     * @param \DateTime|null $downloadTime
     *
     * @return ExcUsrTutor
     */
    public function setDownloadTime($downloadTime = null)
    {
        $this->downloadTime = $downloadTime;

        return $this;
    }

    /**
     * Get downloadTime.
     *
     * @return \DateTime|null
     */
    public function getDownloadTime()
    {
        return $this->downloadTime;
    }
}
