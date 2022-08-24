<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Manages the booking storage of the preference based calculated bookings
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingPrefBasedBookGatewayRepository
{
    protected ilDBInterface $db;

    public function __construct(ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = ($db)
            ?: $DIC->database();
    }

    /**
     * Get pools with overdue preference booking
     * @return int[]
     */
    public function getPoolsWithOverdueBooking(): array
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
     */
    protected function checkProcessHash(int $pool_id): bool
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

        return $rec["pref_booking_hash"] === $hash;
    }

    /**
     * Store bookings
     * see similar code in ilObjBookingPoolGUI::confirmedBookingObject
     * this should got to a reservation repo/manager in the future
     * @param int[][] $bookings
     */
    public function storeBookings(
        int $pool_id,
        array $bookings
    ): void {
        if ($this->checkProcessHash($pool_id)) {
            foreach ($bookings as $user_id => $obj_ids) {
                foreach ($obj_ids as $obj_id) {
                    if (ilBookingReservation::isObjectAvailableNoSchedule($obj_id) &&
                        count(ilBookingReservation::getObjectReservationForUser($obj_id, $user_id)) === 0) { // #18304
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

    public function getBookings(
        array $obj_ids
    ): array {
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
