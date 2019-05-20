<?php



/**
 * History
 */
class History
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
     * @var string|null
     */
    private $objType;

    /**
     * @var string|null
     */
    private $action;

    /**
     * @var \DateTime|null
     */
    private $hdate;

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var string|null
     */
    private $infoParams;

    /**
     * @var string|null
     */
    private $userComment;


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
     * @return History
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
     * Set objType.
     *
     * @param string|null $objType
     *
     * @return History
     */
    public function setObjType($objType = null)
    {
        $this->objType = $objType;

        return $this;
    }

    /**
     * Get objType.
     *
     * @return string|null
     */
    public function getObjType()
    {
        return $this->objType;
    }

    /**
     * Set action.
     *
     * @param string|null $action
     *
     * @return History
     */
    public function setAction($action = null)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return string|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set hdate.
     *
     * @param \DateTime|null $hdate
     *
     * @return History
     */
    public function setHdate($hdate = null)
    {
        $this->hdate = $hdate;

        return $this;
    }

    /**
     * Get hdate.
     *
     * @return \DateTime|null
     */
    public function getHdate()
    {
        return $this->hdate;
    }

    /**
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return History
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
     * Set infoParams.
     *
     * @param string|null $infoParams
     *
     * @return History
     */
    public function setInfoParams($infoParams = null)
    {
        $this->infoParams = $infoParams;

        return $this;
    }

    /**
     * Get infoParams.
     *
     * @return string|null
     */
    public function getInfoParams()
    {
        return $this->infoParams;
    }

    /**
     * Set userComment.
     *
     * @param string|null $userComment
     *
     * @return History
     */
    public function setUserComment($userComment = null)
    {
        $this->userComment = $userComment;

        return $this;
    }

    /**
     * Get userComment.
     *
     * @return string|null
     */
    public function getUserComment()
    {
        return $this->userComment;
    }
}
