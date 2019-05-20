<?php



/**
 * ObjUserDataHist
 */
class ObjUserDataHist
{
    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $updateUser = '0';

    /**
     * @var \DateTime|null
     */
    private $editingTime;


    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return ObjUserDataHist
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
     * @return ObjUserDataHist
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
     * Set updateUser.
     *
     * @param int $updateUser
     *
     * @return ObjUserDataHist
     */
    public function setUpdateUser($updateUser)
    {
        $this->updateUser = $updateUser;

        return $this;
    }

    /**
     * Get updateUser.
     *
     * @return int
     */
    public function getUpdateUser()
    {
        return $this->updateUser;
    }

    /**
     * Set editingTime.
     *
     * @param \DateTime|null $editingTime
     *
     * @return ObjUserDataHist
     */
    public function setEditingTime($editingTime = null)
    {
        $this->editingTime = $editingTime;

        return $this;
    }

    /**
     * Get editingTime.
     *
     * @return \DateTime|null
     */
    public function getEditingTime()
    {
        return $this->editingTime;
    }
}
