<?php



/**
 * CalNotification
 */
class CalNotification
{
    /**
     * @var int
     */
    private $notificationId = '0';

    /**
     * @var int
     */
    private $calId = '0';

    /**
     * @var bool
     */
    private $userType = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string|null
     */
    private $email;


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
     * Set calId.
     *
     * @param int $calId
     *
     * @return CalNotification
     */
    public function setCalId($calId)
    {
        $this->calId = $calId;

        return $this;
    }

    /**
     * Get calId.
     *
     * @return int
     */
    public function getCalId()
    {
        return $this->calId;
    }

    /**
     * Set userType.
     *
     * @param bool $userType
     *
     * @return CalNotification
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;

        return $this;
    }

    /**
     * Get userType.
     *
     * @return bool
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return CalNotification
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
     * Set email.
     *
     * @param string|null $email
     *
     * @return CalNotification
     */
    public function setEmail($email = null)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }
}
