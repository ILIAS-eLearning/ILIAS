<?php



/**
 * RbacLog
 */
class RbacLog
{
    /**
     * @var int
     */
    private $logId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $created = '0';

    /**
     * @var int
     */
    private $refId = '0';

    /**
     * @var bool
     */
    private $action = '0';

    /**
     * @var string|null
     */
    private $data;


    /**
     * Get logId.
     *
     * @return int
     */
    public function getLogId()
    {
        return $this->logId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return RbacLog
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
     * Set created.
     *
     * @param int $created
     *
     * @return RbacLog
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return int
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set refId.
     *
     * @param int $refId
     *
     * @return RbacLog
     */
    public function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Get refId.
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set action.
     *
     * @param bool $action
     *
     * @return RbacLog
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action.
     *
     * @return bool
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set data.
     *
     * @param string|null $data
     *
     * @return RbacLog
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
}
