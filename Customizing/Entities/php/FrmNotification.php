<?php



/**
 * FrmNotification
 */
class FrmNotification
{
    /**
     * @var int
     */
    private $notificationId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $frmId = '0';

    /**
     * @var int
     */
    private $threadId = '0';

    /**
     * @var bool
     */
    private $adminForceNoti = '0';

    /**
     * @var bool
     */
    private $userToggleNoti = '0';

    /**
     * @var int|null
     */
    private $userIdNoti;


    /**
     * Get notificationId.
     *
     * @return int
     */
    public function getNotificationId()
    {
        return $this->notificationId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return FrmNotification
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
     * Set frmId.
     *
     * @param int $frmId
     *
     * @return FrmNotification
     */
    public function setFrmId($frmId)
    {
        $this->frmId = $frmId;

        return $this;
    }

    /**
     * Get frmId.
     *
     * @return int
     */
    public function getFrmId()
    {
        return $this->frmId;
    }

    /**
     * Set threadId.
     *
     * @param int $threadId
     *
     * @return FrmNotification
     */
    public function setThreadId($threadId)
    {
        $this->threadId = $threadId;

        return $this;
    }

    /**
     * Get threadId.
     *
     * @return int
     */
    public function getThreadId()
    {
        return $this->threadId;
    }

    /**
     * Set adminForceNoti.
     *
     * @param bool $adminForceNoti
     *
     * @return FrmNotification
     */
    public function setAdminForceNoti($adminForceNoti)
    {
        $this->adminForceNoti = $adminForceNoti;

        return $this;
    }

    /**
     * Get adminForceNoti.
     *
     * @return bool
     */
    public function getAdminForceNoti()
    {
        return $this->adminForceNoti;
    }

    /**
     * Set userToggleNoti.
     *
     * @param bool $userToggleNoti
     *
     * @return FrmNotification
     */
    public function setUserToggleNoti($userToggleNoti)
    {
        $this->userToggleNoti = $userToggleNoti;

        return $this;
    }

    /**
     * Get userToggleNoti.
     *
     * @return bool
     */
    public function getUserToggleNoti()
    {
        return $this->userToggleNoti;
    }

    /**
     * Set userIdNoti.
     *
     * @param int|null $userIdNoti
     *
     * @return FrmNotification
     */
    public function setUserIdNoti($userIdNoti = null)
    {
        $this->userIdNoti = $userIdNoti;

        return $this;
    }

    /**
     * Get userIdNoti.
     *
     * @return int|null
     */
    public function getUserIdNoti()
    {
        return $this->userIdNoti;
    }
}
