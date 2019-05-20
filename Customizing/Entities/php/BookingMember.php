<?php



/**
 * BookingMember
 */
class BookingMember
{
    /**
     * @var int
     */
    private $participantId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var string
     */
    private $bookingPoolId;

    /**
     * @var int
     */
    private $assignerUserId = '0';


    /**
     * Set participantId.
     *
     * @param int $participantId
     *
     * @return BookingMember
     */
    public function setParticipantId($participantId)
    {
        $this->participantId = $participantId;

        return $this;
    }

    /**
     * Get participantId.
     *
     * @return int
     */
    public function getParticipantId()
    {
        return $this->participantId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return BookingMember
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
     * Set bookingPoolId.
     *
     * @param string $bookingPoolId
     *
     * @return BookingMember
     */
    public function setBookingPoolId($bookingPoolId)
    {
        $this->bookingPoolId = $bookingPoolId;

        return $this;
    }

    /**
     * Get bookingPoolId.
     *
     * @return string
     */
    public function getBookingPoolId()
    {
        return $this->bookingPoolId;
    }

    /**
     * Set assignerUserId.
     *
     * @param int $assignerUserId
     *
     * @return BookingMember
     */
    public function setAssignerUserId($assignerUserId)
    {
        $this->assignerUserId = $assignerUserId;

        return $this;
    }

    /**
     * Get assignerUserId.
     *
     * @return int
     */
    public function getAssignerUserId()
    {
        return $this->assignerUserId;
    }
}
