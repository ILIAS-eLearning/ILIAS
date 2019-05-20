<?php



/**
 * UsrSession
 */
class UsrSession
{
    /**
     * @var string
     */
    private $sessionId = ' ';

    /**
     * @var int
     */
    private $expires = '0';

    /**
     * @var string|null
     */
    private $data;

    /**
     * @var int
     */
    private $ctime = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $lastRemindTs = '0';

    /**
     * @var int|null
     */
    private $type;

    /**
     * @var int|null
     */
    private $createtime;

    /**
     * @var string|null
     */
    private $remoteAddr;

    /**
     * @var string|null
     */
    private $context;


    /**
     * Get sessionId.
     *
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * Set expires.
     *
     * @param int $expires
     *
     * @return UsrSession
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * Get expires.
     *
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * Set data.
     *
     * @param string|null $data
     *
     * @return UsrSession
     */
    public function setData($data = null)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data.
     *
     * @return string|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set ctime.
     *
     * @param int $ctime
     *
     * @return UsrSession
     */
    public function setCtime($ctime)
    {
        $this->ctime = $ctime;

        return $this;
    }

    /**
     * Get ctime.
     *
     * @return int
     */
    public function getCtime()
    {
        return $this->ctime;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return UsrSession
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
     * Set lastRemindTs.
     *
     * @param int $lastRemindTs
     *
     * @return UsrSession
     */
    public function setLastRemindTs($lastRemindTs)
    {
        $this->lastRemindTs = $lastRemindTs;

        return $this;
    }

    /**
     * Get lastRemindTs.
     *
     * @return int
     */
    public function getLastRemindTs()
    {
        return $this->lastRemindTs;
    }

    /**
     * Set type.
     *
     * @param int|null $type
     *
     * @return UsrSession
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set createtime.
     *
     * @param int|null $createtime
     *
     * @return UsrSession
     */
    public function setCreatetime($createtime = null)
    {
        $this->createtime = $createtime;

        return $this;
    }

    /**
     * Get createtime.
     *
     * @return int|null
     */
    public function getCreatetime()
    {
        return $this->createtime;
    }

    /**
     * Set remoteAddr.
     *
     * @param string|null $remoteAddr
     *
     * @return UsrSession
     */
    public function setRemoteAddr($remoteAddr = null)
    {
        $this->remoteAddr = $remoteAddr;

        return $this;
    }

    /**
     * Get remoteAddr.
     *
     * @return string|null
     */
    public function getRemoteAddr()
    {
        return $this->remoteAddr;
    }

    /**
     * Set context.
     *
     * @param string|null $context
     *
     * @return UsrSession
     */
    public function setContext($context = null)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context.
     *
     * @return string|null
     */
    public function getContext()
    {
        return $this->context;
    }
}
