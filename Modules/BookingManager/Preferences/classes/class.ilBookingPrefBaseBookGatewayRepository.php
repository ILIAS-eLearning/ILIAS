<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Manages the booking storage of the preference based calculated bookings
 *
 * @author killing@leifos.de
 */
class ilBookingPrefBasedBookGatewayRepository
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct(ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = ($db)
            ? $db
            : $DIC->database();
    }

    /**
     * Get pools with overdue preference booking
     *
     * @return int[]
     */
    public function getPoolsWithOverdueBooking()
    {
        $db = $this->db;

        $pool_ids = [];
        $set = $db->queryF(
            "SELECT booking_pool_id FROM booking_settings " .
            " WHERE schedule_type = %s " .
            " AND pref_deadline < %s " .
            " AND pref_booking_hash	= %s ",
            array("integer", "integer", "text"),
            array(ilObjBookingPool::TYPE_NO_SCHEDULE_PREFERENCES, time(), "0")
        );
        while ($rec = $db->fetchAssoc($set)) {
            $pool_ids[] = $rec["booking_pool_id"];
        }
        return $pool_ids;
    }
    
    
    /**
     * Semaphore like hash setting/checking to ensure that no
     * other process is doing the same
     *
     * @return bool
     */
    protected function checkProcessHash($pool_id)
    {
        $db = $this->db;

        $hash = uniqid("", true);

        $db->update("booking_settings", array(
                "pref_booking_hash" => array("text", $hash)
            ), array(	// where
                "booking_pool_id" => array("integer", $pool_id),
                "pref_booking_hash" => array("text", "0"),
            ));

        $set = $db->queryF(
            "SELECT pref_booking_hash FROM booking_settings " .
            " WHERE booking_pool_id = %s ",
            array("integer"),
            array($pool_id)
        );
        $rec = $db->fetchAssoc($set);

        if ($rec["pref_booking_hash"] == $hash) {
            return true;
        }
        return false;
    }

    public function hasRun($pool_id) : bool
    {
        $db = $this->db;
        $set = $db->queryF(
            "SELECT pref_booking_hash FROM booking_settings " .
            " WHERE booking_pool_id = %s ",
            array("integer"),
            array($pool_id)
        );
        $rec = $db->fetchAssoc($set);

        if ($rec["pref_booking_hash"] !== "0") {
            return true;
        }
        return false;
    }

    public function resetRun($pool_id) : void
    {
        $db = $this->db;
        $db->update("booking_settings", array(
            "pref_booking_hash" => array("text", "0")
        ), array(	// where
                     "booking_pool_id" => array("integer", $pool_id)
        ));
    }

    /**
     * Store bookings
     * see similar code in ilObjBookingPoolGUI::confirmedBookingObject
     * this should got to a reservation repo/manager in the future
     *
     * @param int[][] $bookings
     */
    public function storeBookings(int $pool_id, $bookings)
    {
        if ($this->checkProcessHash($pool_id)) {
            foreach ($bookings as $user_id => $obj_ids) {
                foreach ($obj_ids as $obj_id) {
                    if (ilBookingReservation::isObjectAvailableNoSchedule($obj_id) &&
                        !ilBookingReservation::getObjectReservationForUser($obj_id, $user_id)) { // #18304
                        $reservation = new ilBookingReservation();
                        $reservation->setObjectId($obj_id);
                        $reservation->setUserId($user_id);
                        $reservation->setAssignerId($user_id);
                        $reservation->setFrom(null);
                        $reservation->setTo(null);
                        $reservation->save();
                    }
                }
            }
        }
    }

    /**
     * Get bookings
     *
     * @param array $obj_ids
     * @return array
     */
    public function getBookings(array $obj_ids)
    {
        $bookings = [];
        foreach (ilBookingReservation::getList(
            $obj_ids,
            10000,
            0,
            ["status" => -ilBookingReservation::STATUS_CANCELLED]
        )["data"] as $book) {
            $bookings[$book["user_id"]][] = $book["object_id"];
        }
        return $bookings;
    }
}
