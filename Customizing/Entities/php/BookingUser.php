<?php



/**
 * BookingUser
 */
class BookingUser
{
    /**
     * @var int
     */
    private $entryId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $tstamp = '0';

    /**
     * @var string|null
     */
    private $bookingMessage;

    /**
     * @var bool
     */
    private $notificationSent = '0';


    /**
     * Set entryId.
     *
     * @param int $entryId
     *
     * @return BookingUser
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;

        return $this;
    }

    /**
     * Get entryId.
     *
     * @return int
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return BookingUser
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
     * Set tstamp.
     *
     * @param int $tstamp
     *
     * @return BookingUser
     */
    public function setTstamp($tstamp)
    {
        $this->tstamp = $tstamp;

        return $this;
    }

    /**
     * Get tstamp.
     *
     * @return int
     */
    public function getTstamp()
    {
        return $this->tstamp;
    }

    /**
     * Set bookingMessage.
     *
     * @param string|null $bookingMessage
     *
     * @return BookingUser
     */
    public function setBookingMessage($bookingMessage = null)
    {
        $this->bookingMessage = $bookingMessage;

        return $this;
    }

    /**
     * Get bookingMessage.
     *
     * @return string|null
     */
    public function getBookingMessage()
    {
        return $this->bookingMessage;
    }

    /**
     * Set notificationSent.
     *
     * @param bool $notificationSent
     *
     * @return BookingUser
     */
    public function setNotificationSent($notificationSent)
    {
        $this->notificationSent = $notificationSent;

        return $this;
    }

    /**
     * Get notificationSent.
     *
     * @return bool
     */
    public function getNotificationSent()
    {
        return $this->notificationSent;
    }
}
