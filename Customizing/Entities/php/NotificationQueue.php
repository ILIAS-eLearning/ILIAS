<?php



/**
 * NotificationQueue
 */
class NotificationQueue
{
    /**
     * @var int
     */
    private $notificationId = '0';

    /**
     * @var int
     */
    private $usrId = '0';

    /**
     * @var int
     */
    private $validUntil = '0';


    /**
     * Set notificationId.
     *
     * @param int $notificationId
     *
     * @return NotificationQueue
     */
    public function setNotificationId($notificationId)
    {
        $this->notificationId = $notificationId;

        return $this;
    }

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
     * Set usrId.
     *
     * @param int $usrId
     *
     * @return NotificationQueue
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
     * Set validUntil.
     *
     * @param int $validUntil
     *
     * @return NotificationQueue
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    /**
     * Get validUntil.
     *
     * @return int
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }
}
