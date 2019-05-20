<?php



/**
 * MailSaved
 */
class MailSaved
{
    /**
     * @var int
     */
    private $userId = '0';

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
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set mType.
     *
     * @param string|null $mType
     *
     * @return MailSaved
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
     * @return MailSaved
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
     * @return MailSaved
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
     * Set usePlaceholders.
     *
     * @param bool $usePlaceholders
     *
     * @return MailSaved
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
     * @return MailSaved
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
     * @return MailSaved
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
     * @return MailSaved
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
     * @return MailSaved
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
     * @return MailSaved
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
     * @return MailSaved
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
     * @return MailSaved
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
