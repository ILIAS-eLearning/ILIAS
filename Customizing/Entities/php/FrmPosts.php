<?php



/**
 * FrmPosts
 */
class FrmPosts
{
    /**
     * @var int
     */
    private $posPk = '0';

    /**
     * @var int
     */
    private $posTopFk = '0';

    /**
     * @var int
     */
    private $posThrFk = '0';

    /**
     * @var string|null
     */
    private $posUsrAlias;

    /**
     * @var string|null
     */
    private $posSubject;

    /**
     * @var \DateTime|null
     */
    private $posDate;

    /**
     * @var \DateTime|null
     */
    private $posUpdate;

    /**
     * @var int
     */
    private $updateUser = '0';

    /**
     * @var bool
     */
    private $posCens = '0';

    /**
     * @var string|null
     */
    private $posCensCom;

    /**
     * @var bool
     */
    private $notify = '0';

    /**
     * @var string|null
     */
    private $importName;

    /**
     * @var bool
     */
    private $posStatus = '1';

    /**
     * @var string|null
     */
    private $posMessage;

    /**
     * @var int
     */
    private $posAuthorId = '0';

    /**
     * @var int
     */
    private $posDisplayUserId = '0';

    /**
     * @var bool|null
     */
    private $isAuthorModerator;

    /**
     * @var \DateTime|null
     */
    private $posCensDate;

    /**
     * @var \DateTime|null
     */
    private $posActivationDate;


    /**
     * Get posPk.
     *
     * @return int
     */
    public function getPosPk()
    {
        return $this->posPk;
    }

    /**
     * Set posTopFk.
     *
     * @param int $posTopFk
     *
     * @return FrmPosts
     */
    public function setPosTopFk($posTopFk)
    {
        $this->posTopFk = $posTopFk;

        return $this;
    }

    /**
     * Get posTopFk.
     *
     * @return int
     */
    public function getPosTopFk()
    {
        return $this->posTopFk;
    }

    /**
     * Set posThrFk.
     *
     * @param int $posThrFk
     *
     * @return FrmPosts
     */
    public function setPosThrFk($posThrFk)
    {
        $this->posThrFk = $posThrFk;

        return $this;
    }

    /**
     * Get posThrFk.
     *
     * @return int
     */
    public function getPosThrFk()
    {
        return $this->posThrFk;
    }

    /**
     * Set posUsrAlias.
     *
     * @param string|null $posUsrAlias
     *
     * @return FrmPosts
     */
    public function setPosUsrAlias($posUsrAlias = null)
    {
        $this->posUsrAlias = $posUsrAlias;

        return $this;
    }

    /**
     * Get posUsrAlias.
     *
     * @return string|null
     */
    public function getPosUsrAlias()
    {
        return $this->posUsrAlias;
    }

    /**
     * Set posSubject.
     *
     * @param string|null $posSubject
     *
     * @return FrmPosts
     */
    public function setPosSubject($posSubject = null)
    {
        $this->posSubject = $posSubject;

        return $this;
    }

    /**
     * Get posSubject.
     *
     * @return string|null
     */
    public function getPosSubject()
    {
        return $this->posSubject;
    }

    /**
     * Set posDate.
     *
     * @param \DateTime|null $posDate
     *
     * @return FrmPosts
     */
    public function setPosDate($posDate = null)
    {
        $this->posDate = $posDate;

        return $this;
    }

    /**
     * Get posDate.
     *
     * @return \DateTime|null
     */
    public function getPosDate()
    {
        return $this->posDate;
    }

    /**
     * Set posUpdate.
     *
     * @param \DateTime|null $posUpdate
     *
     * @return FrmPosts
     */
    public function setPosUpdate($posUpdate = null)
    {
        $this->posUpdate = $posUpdate;

        return $this;
    }

    /**
     * Get posUpdate.
     *
     * @return \DateTime|null
     */
    public function getPosUpdate()
    {
        return $this->posUpdate;
    }

    /**
     * Set updateUser.
     *
     * @param int $updateUser
     *
     * @return FrmPosts
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
     * Set posCens.
     *
     * @param bool $posCens
     *
     * @return FrmPosts
     */
    public function setPosCens($posCens)
    {
        $this->posCens = $posCens;

        return $this;
    }

    /**
     * Get posCens.
     *
     * @return bool
     */
    public function getPosCens()
    {
        return $this->posCens;
    }

    /**
     * Set posCensCom.
     *
     * @param string|null $posCensCom
     *
     * @return FrmPosts
     */
    public function setPosCensCom($posCensCom = null)
    {
        $this->posCensCom = $posCensCom;

        return $this;
    }

    /**
     * Get posCensCom.
     *
     * @return string|null
     */
    public function getPosCensCom()
    {
        return $this->posCensCom;
    }

    /**
     * Set notify.
     *
     * @param bool $notify
     *
     * @return FrmPosts
     */
    public function setNotify($notify)
    {
        $this->notify = $notify;

        return $this;
    }

    /**
     * Get notify.
     *
     * @return bool
     */
    public function getNotify()
    {
        return $this->notify;
    }

    /**
     * Set importName.
     *
     * @param string|null $importName
     *
     * @return FrmPosts
     */
    public function setImportName($importName = null)
    {
        $this->importName = $importName;

        return $this;
    }

    /**
     * Get importName.
     *
     * @return string|null
     */
    public function getImportName()
    {
        return $this->importName;
    }

    /**
     * Set posStatus.
     *
     * @param bool $posStatus
     *
     * @return FrmPosts
     */
    public function setPosStatus($posStatus)
    {
        $this->posStatus = $posStatus;

        return $this;
    }

    /**
     * Get posStatus.
     *
     * @return bool
     */
    public function getPosStatus()
    {
        return $this->posStatus;
    }

    /**
     * Set posMessage.
     *
     * @param string|null $posMessage
     *
     * @return FrmPosts
     */
    public function setPosMessage($posMessage = null)
    {
        $this->posMessage = $posMessage;

        return $this;
    }

    /**
     * Get posMessage.
     *
     * @return string|null
     */
    public function getPosMessage()
    {
        return $this->posMessage;
    }

    /**
     * Set posAuthorId.
     *
     * @param int $posAuthorId
     *
     * @return FrmPosts
     */
    public function setPosAuthorId($posAuthorId)
    {
        $this->posAuthorId = $posAuthorId;

        return $this;
    }

    /**
     * Get posAuthorId.
     *
     * @return int
     */
    public function getPosAuthorId()
    {
        return $this->posAuthorId;
    }

    /**
     * Set posDisplayUserId.
     *
     * @param int $posDisplayUserId
     *
     * @return FrmPosts
     */
    public function setPosDisplayUserId($posDisplayUserId)
    {
        $this->posDisplayUserId = $posDisplayUserId;

        return $this;
    }

    /**
     * Get posDisplayUserId.
     *
     * @return int
     */
    public function getPosDisplayUserId()
    {
        return $this->posDisplayUserId;
    }

    /**
     * Set isAuthorModerator.
     *
     * @param bool|null $isAuthorModerator
     *
     * @return FrmPosts
     */
    public function setIsAuthorModerator($isAuthorModerator = null)
    {
        $this->isAuthorModerator = $isAuthorModerator;

        return $this;
    }

    /**
     * Get isAuthorModerator.
     *
     * @return bool|null
     */
    public function getIsAuthorModerator()
    {
        return $this->isAuthorModerator;
    }

    /**
     * Set posCensDate.
     *
     * @param \DateTime|null $posCensDate
     *
     * @return FrmPosts
     */
    public function setPosCensDate($posCensDate = null)
    {
        $this->posCensDate = $posCensDate;

        return $this;
    }

    /**
     * Get posCensDate.
     *
     * @return \DateTime|null
     */
    public function getPosCensDate()
    {
        return $this->posCensDate;
    }

    /**
     * Set posActivationDate.
     *
     * @param \DateTime|null $posActivationDate
     *
     * @return FrmPosts
     */
    public function setPosActivationDate($posActivationDate = null)
    {
        $this->posActivationDate = $posActivationDate;

        return $this;
    }

    /**
     * Get posActivationDate.
     *
     * @return \DateTime|null
     */
    public function getPosActivationDate()
    {
        return $this->posActivationDate;
    }
}
