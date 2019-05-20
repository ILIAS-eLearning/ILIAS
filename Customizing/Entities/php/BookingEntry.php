<?php



/**
 * BookingEntry
 */
class BookingEntry
{
    /**
     * @var int
     */
    private $bookingId = '0';

    /**
     * @var int
     */
    private $objId = '0';

    /**
     * @var int
     */
    private $deadline = '0';

    /**
     * @var int
     */
    private $numBookings = '0';

    /**
     * @var int|null
     */
    private $targetObjId;

    /**
     * @var int
     */
    private $bookingGroup = '0';


    /**
     * Get bookingId.
     *
     * @return int
     */
    public function getBookingId()
    {
        return $this->bookingId;
    }

    /**
     * Set objId.
     *
     * @param int $objId
     *
     * @return BookingEntry
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId.
     *
     * @return int
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set deadline.
     *
     * @param int $deadline
     *
     * @return BookingEntry
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;

        return $this;
    }

    /**
     * Get deadline.
     *
     * @return int
     */
    public function getDeadline()
    {
        return $this->deadline;
    }

    /**
     * Set numBookings.
     *
     * @param int $numBookings
     *
     * @return BookingEntry
     */
    public function setNumBookings($numBookings)
    {
        $this->numBookings = $numBookings;

        return $this;
    }

    /**
     * Get numBookings.
     *
     * @return int
     */
    public function getNumBookings()
    {
        return $this->numBookings;
    }

    /**
     * Set targetObjId.
     *
     * @param int|null $targetObjId
     *
     * @return BookingEntry
     */
    public function setTargetObjId($targetObjId = null)
    {
        $this->targetObjId = $targetObjId;

        return $this;
    }

    /**
     * Get targetObjId.
     *
     * @return int|null
     */
    public function getTargetObjId()
    {
        return $this->targetObjId;
    }

    /**
     * Set bookingGroup.
     *
     * @param int $bookingGroup
     *
     * @return BookingEntry
     */
    public function setBookingGroup($bookingGroup)
    {
        $this->bookingGroup = $bookingGroup;

        return $this;
    }

    /**
     * Get bookingGroup.
     *
     * @return int
     */
    public function getBookingGroup()
    {
        return $this->bookingGroup;
    }
}
