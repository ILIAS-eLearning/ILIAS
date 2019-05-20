<?php



/**
 * Mail
 */
class Mail
{
    /**
     * @var int
     */
    private $mailId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $folderId = '0';

    /**
     * @var int|null
     */
    private $senderId;

    /**
     * @var \DateTime|null
     */
    private $sendTime;

    /**
     * @var string|null
     */
    private $mStatus;

    /**
     * @var string|null
     */
    private $mType;

    /**
     * @var bool|null
     */
    private $mEmail;

    /**
     * @var string|null
     */
    private $mSubject;

    /**
     * @var string|null
     */
    private $importName;

    /**
     * @var bool
     */
    private $usePlaceholders = '0';

    /**
     * @var string|null
     */
    private $mMessage;

    /**
     * @var string|null
     */
    private $rcpTo;

    /**
     * @var string|null
     */
    private $rcpCc;

    /**
     * @var string|null
     */
    private $rcpBcc;

    /**
     * @var string|null
     */
    private $attachments;

    /**
     * @var string|null
     */
    private $tplCtxId;

    /**
     * @var string|null
     */
    private $tplCtxParams;


    /**
     * Get mailId.
     *
     * @return int
     */
    public function getMailId()
    {
        return $this->mailId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return Mail
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set folderId.
     *
     * @param int $folderId
     *
     * @return Mail
     */
    public function setFolderId($folderId)
    {
        $this->folderId = $folderId;

        return $this;
    }

    /**
     * Get folderId.
     *
     * @return int
     */
    public function getFolderId()
    {
        return $this->folderId;
    }

    /**
     * Set senderId.
     *
     * @param int|null $senderId
     *
     * @return Mail
     */
    public function setSenderId($senderId = null)
    {
        $this->senderId = $senderId;

        return $this;
    }

    /**
     * Get senderId.
     *
     * @return int|null
     */
    public function getSenderId()
    {
        return $this->senderId;
    }

    /**
     * Set sendTime.
     *
     * @param \DateTime|null $sendTime
     *
     * @return Mail
     */
    public function setSendTime($sendTime = null)
    {
        $this->sendTime = $sendTime;

        return $this;
    }

    /**
     * Get sendTime.
     *
     * @return \DateTime|null
     */
    public function getSendTime()
    {
        return $this->sendTime;
    }

    /**
     * Set mStatus.
     *
     * @param string|null $mStatus
     *
     * @return Mail
     */
    public function setMStatus($mStatus = null)
    {
        $this->mStatus = $mStatus;

        return $this;
    }

    /**
     * Get mStatus.
     *
     * @return string|null
     */
    public function getMStatus()
    {
        return $this->mStatus;
    }

    /**
     * Set mType.
     *
     * @param string|null $mType
     *
     * @return Mail
     */
    public function setMType($mType = null)
    {
        $this->mType = $mType;

        return $this;
    }

    /**
     * Get mType.
     *
     * @return string|null
     */
    public function getMType()
    {
        return $this->mType;
    }

    /**
     * Set mEmail.
     *
     * @param bool|null $mEmail
     *
     * @return Mail
     */
    public function setMEmail($mEmail = null)
    {
        $this->mEmail = $mEmail;

        return $this;
    }

    /**
     * Get mEmail.
     *
     * @return bool|null
     */
    public function getMEmail()
    {
        return $this->mEmail;
    }

    /**
     * Set mSubject.
     *
     * @param string|null $mSubject
     *
     * @return Mail
     */
    public function setMSubject($mSubject = null)
    {
        $this->mSubject = $mSubject;

        return $this;
    }

    /**
     * Get mSubject.
     *
     * @return string|null
     */
    public function getMSubject()
    {
        return $this->mSubject;
    }

    /**
     * Set importName.
     *
     * @param string|null $importName
     *
     * @return Mail
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
     * Set usePlaceholders.
     *
     * @param bool $usePlaceholders
     *
     * @return Mail
     */
    public function setUsePlaceholders($usePlaceholders)
    {
        $this->usePlaceholders = $usePlaceholders;

        return $this;
    }

    /**
     * Get usePlaceholders.
     *
     * @return bool
     */
    public function getUsePlaceholders()
    {
        return $this->usePlaceholders;
    }

    /**
     * Set mMessage.
     *
     * @param string|null $mMessage
     *
     * @return Mail
     */
    public function setMMessage($mMessage = null)
    {
        $this->mMessage = $mMessage;

        return $this;
    }

    /**
     * Get mMessage.
     *
     * @return string|null
     */
    public function getMMessage()
    {
        return $this->mMessage;
    }

    /**
     * Set rcpTo.
     *
     * @param string|null $rcpTo
     *
     * @return Mail
     */
    public function setRcpTo($rcpTo = null)
    {
        $this->rcpTo = $rcpTo;

        return $this;
    }

    /**
     * Get rcpTo.
     *
     * @return string|null
     */
    public function getRcpTo()
    {
        return $this->rcpTo;
    }

    /**
     * Set rcpCc.
     *
     * @param string|null $rcpCc
     *
     * @return Mail
     */
    public function setRcpCc($rcpCc = null)
    {
        $this->rcpCc = $rcpCc;

        return $this;
    }

    /**
     * Get rcpCc.
     *
     * @return string|null
     */
    public function getRcpCc()
    {
        return $this->rcpCc;
    }

    /**
     * Set rcpBcc.
     *
     * @param string|null $rcpBcc
     *
     * @return Mail
     */
    public function setRcpBcc($rcpBcc = null)
    {
        $this->rcpBcc = $rcpBcc;

        return $this;
    }

    /**
     * Get rcpBcc.
     *
     * @return string|null
     */
    public function getRcpBcc()
    {
        return $this->rcpBcc;
    }

    /**
     * Set attachments.
     *
     * @param string|null $attachments
     *
     * @return Mail
     */
    public function setAttachments($attachments = null)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get attachments.
     *
     * @return string|null
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set tplCtxId.
     *
     * @param string|null $tplCtxId
     *
     * @return Mail
     */
    public function setTplCtxId($tplCtxId = null)
    {
        $this->tplCtxId = $tplCtxId;

        return $this;
    }

    /**
     * Get tplCtxId.
     *
     * @return string|null
     */
    public function getTplCtxId()
    {
        return $this->tplCtxId;
    }

    /**
     * Set tplCtxParams.
     *
     * @param string|null $tplCtxParams
     *
     * @return Mail
     */
    public function setTplCtxParams($tplCtxParams = null)
    {
        $this->tplCtxParams = $tplCtxParams;

        return $this;
    }

    /**
     * Get tplCtxParams.
     *
     * @return string|null
     */
    public function getTplCtxParams()
    {
        return $this->tplCtxParams;
    }
}
