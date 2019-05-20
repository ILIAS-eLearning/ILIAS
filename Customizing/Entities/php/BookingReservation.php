<?php



/**
 * BookingReservation
 */
class BookingReservation
{
    /**
     * @var int
     */
    private $bookingReservationId = '0';

    /**
     * @var int
     */
    private $userId = '0';

    /**
     * @var int
     */
    private $objectId = '0';

    /**
     * @var int
     */
    private $dateFrom = '0';

    /**
     * @var int
     */
    private $dateTo = '0';

    /**
     * @var int|null
     */
    private $status;

    /**
     * @var int|null
     */
    private $groupId;

    /**
     * @var int
     */
    private $assignerId = '0';


    /**
     * Get bookingReservationId.
     *
     * @return int
     */
    public function getBookingReservationId()
    {
        return $this->bookingReservationId;
    }

    /**
     * Set userId.
     *
     * @param int $userId
     *
     * @return BookingReservation
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
     * Set objectId.
     *
     * @param int $objectId
     *
     * @return BookingReservation
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId.
     *
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set dateFrom.
     *
     * @param int $dateFrom
     *
     * @return BookingReservation
     */
    public function setDateFrom($dateFrom)
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    /**
     * Get dateFrom.
     *
     * @return int
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * Set dateTo.
     *
     * @param int $dateTo
     *
     * @return BookingReservation
     */
    public function setDateTo($dateTo)
    {
        $this->dateTo = $dateTo;

        return $this;
    }

    /**
     * Get dateTo.
     *
     * @return int
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * Set status.
     *
     * @param int|null $status
     *
     * @return BookingReservation
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set groupId.
     *
     * @param int|null $groupId
     *
     * @return BookingReservation
     */
    public function setGroupId($groupId = null)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get groupId.
     *
     * @return int|null
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set assignerId.
     *
     * @param int $assignerId
     *
     * @return BookingReservation
     */
    public function setAssignerId($assignerId)
    {
        $this->assignerId = $assignerId;

        return $this;
    }

    /**
     * Get assignerId.
     *
     * @return int
     */
    public function getAssignerId()
    {
        return $this->assignerId;
    }
}
