<?php



/**
 * BookingObjAssignment
 */
class BookingObjAssignment
{
    /**
     * @var int
     */
    private $bookingId = '0';

    /**
     * @var int
     */
    private $targetObjId = '0';


    /**
     * Set bookingId.
     *
     * @param int $bookingId
     *
     * @return BookingObjAssignment
     */
    public function setBookingId($bookingId)
    {
        $this->bookingId = $bookingId;

        return $this;
    }

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
     * Set targetObjId.
     *
     * @param int $targetObjId
     *
     * @return BookingObjAssignment
     */
    public function setTargetObjId($targetObjId)
    {
        $this->targetObjId = $targetObjId;

        return $this;
    }

    /**
     * Get targetObjId.
     *
     * @return int
     */
    public function getTargetObjId()
    {
        return $this->targetObjId;
    }
}
