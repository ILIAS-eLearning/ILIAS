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
 * @author Jesús López <lopez@leifos.com>
 */
class ilBookingParticipant
{
    protected ilLanguage $lng;
    protected ilDBInterface $db;
    protected int $participant_id;
    protected int $booking_pool_id;
    protected bool $is_new;
    protected ilObjUser $user;

    public function __construct(
        int $a_user_id,
        int $a_booking_pool_id
    ) {
        global $DIC;

        if (!ilObjUser::_exists($a_user_id)) {
            throw new ilException("User $a_user_id does not exist.");
        }
        if (!ilObjBookingPool::_exists($a_booking_pool_id)) {
            throw new ilException("Booking Pool $a_booking_pool_id does not exist.");
        }

        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->user = $DIC->user();

        $this->participant_id = $a_user_id;
        $this->booking_pool_id = $a_booking_pool_id;

        // if read and not exists, store it in db.
        if (!$this->read()) {
            $this->save();
            $this->is_new = true;
        } else {
            $this->is_new = false;
        }
    }

    protected function read(): ?int
    {
        $query = 'SELECT participant_id FROM booking_member' .
            ' WHERE user_id = ' . $this->db->quote($this->participant_id, 'integer') .
            ' AND booking_pool_id = ' . $this->db->quote($this->booking_pool_id, 'integer');

        $set = $this->db->query($query);
        $row = $this->db->fetchAssoc($set);
        if (empty($row)) {
            return null;
        }
        return (int) $row['participant_id'];
    }

    protected function save(): void
    {
        $assigner_id = $this->user->getId();
        $next_id = $this->db->nextId('booking_member');

        $query = 'INSERT INTO booking_member' .
            ' (participant_id, user_id, booking_pool_id, assigner_user_id)' .
            ' VALUES (' . $this->db->quote($next_id, 'integer') .
            ',' . $this->db->quote($this->participant_id, 'integer') .
            ',' . $this->db->quote($this->booking_pool_id, 'integer') .
            ',' . $this->db->quote($assigner_id, 'integer') . ')';

        $this->db->manipulate($query);
    }

    public function getIsNew(): bool
    {
        return $this->is_new;
    }

    /**
     * Get participants who can not have a reservation for this booking pool object id.
     * @param int $a_bp_object_id booking pool object
     * @return array formatted data to display in gui table.
     */
    public static function getAssignableParticipants(
        int $a_bp_object_id
    ): array {
        $booking_object = new ilBookingObject($a_bp_object_id);
        $pool_id = $booking_object->getPoolId();
        $pool = new ilObjBookingPool($pool_id, false);
        $overall_limit = (int) $pool->getOverallLimit();

        $res = array();

        $members = ilBookingReservation::getMembersWithoutReservation($a_bp_object_id);

        foreach ($members as $member_id) {
            //check if the user reached the limit of booking in this booking pool.
            $total_reservations = ilBookingReservation::isBookingPoolLimitReachedByUser($member_id, $pool_id);

            if ($overall_limit === 0 || ($overall_limit > 0 && $total_reservations < $overall_limit)) {
                $user_name = ilObjUser::_lookupName($member_id);
                $name = $user_name['lastname'] . ", " . $user_name['firstname'];
                $index = $a_bp_object_id . "_" . $member_id;

                if (!isset($res[$index])) {
                    $res[$index] = array(
                        "user_id" => $member_id,
                        "object_title" => array($booking_object->getTitle()),
                        "name" => $name
                    );
                } elseif (!in_array($booking_object->getTitle(), $res[$index]['object_title'], true)) {
                    $res[$index]['object_title'][] = $booking_object->getTitle();
                }
            }
        }

        return $res;
    }

    public static function getList(
        int $a_booking_pool,
        array $a_filter = null,
        int $a_object_id = null
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $res = array();

        $query = 'SELECT bm.user_id, bm.booking_pool_id, br.object_id, bo.title, br.status' .
            ' FROM booking_member bm' .
            ' LEFT JOIN booking_reservation br ON (bm.user_id = br.user_id)' .
            ' LEFT JOIN booking_object bo ON (br.object_id = bo.booking_object_id AND bo.pool_id = ' . $ilDB->quote($a_booking_pool, 'integer') . ')';

        $where = array('bm.booking_pool_id =' . $ilDB->quote($a_booking_pool, 'integer'));
        if ($a_object_id) {
            $where[] = 'br.object_id = ' . $ilDB->quote($a_object_id, 'integer');
        }
        if ($a_filter['title'] ?? false) {
            $where[] = '(' . $ilDB->like('title', 'text', '%' . $a_filter['title'] . '%') .
                ' OR ' . $ilDB->like('description', 'text', '%' . $a_filter['title'] . '%') . ')';
        }
        if ($a_filter['user_id'] ?? false) {
            $where[] = 'bm.user_id = ' . $ilDB->quote($a_filter['user_id'], 'integer');
        }

        $query .= ' WHERE ' . implode(' AND ', $where);

        $set = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($set)) {
            $status = $row['status'];
            //Nothing to show if the status is canceled when filtering by object
            if ($status == ilBookingReservation::STATUS_CANCELLED && $a_object_id) {
                continue;
            }

            $user_name = ilObjUser::_lookupName($row['user_id']);
            $name = $user_name['lastname'] . ", " . $user_name['firstname'];
            $index = $a_booking_pool . "_" . $row['user_id'];
            $actions = array();

            if (!isset($res[$index])) {
                $res[$index] = array(
                    "object_title" => array(),
                    "name" => $name
                );

                if ($status != ilBookingReservation::STATUS_CANCELLED && $row['title'] !== "") {
                    $res[$index]['object_title'] = array($row['title']);
                    $res[$index]['obj_count'] = 1;
                    $res[$index]['object_ids'][] = $row['object_id'];
                }
            } elseif ($row['title'] !== "" && (!in_array($row['title'], $res[$index]['object_title'], true) && $status != ilBookingReservation::STATUS_CANCELLED)) {
                $res[$index]['object_title'][] = $row['title'];
                if (!isset($res[$index]['obj_count'])) {
                    $res[$index]['obj_count'] = 0;
                }
                $res[$index]['obj_count'] += 1;
                $res[$index]['object_ids'][] = $row['object_id'];
            }
            $res[$index]['user_id'] = $row['user_id'];
        }

        foreach ($res as $index => $val) {
            if (isset($row['object_id'])) {
                $res[$index]['object_ids'][] = $row['object_id'];
            }
        }
        return $res;
    }

    /**
     * Get all participants for a booking pool
     * @return int[]
     */
    public static function getBookingPoolParticipants(
        int $a_booking_pool_id
    ): array {
        global $DIC;
        $ilDB = $DIC->database();
        $sql = 'SELECT * FROM booking_member WHERE booking_pool_id = ' . $ilDB->quote($a_booking_pool_id, 'integer');

        $set = $ilDB->query($sql);

        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row['user_id'];
        }

        return $res;
    }

    /**
     * Get user data from db for an specific pool id.
     * @return string[]
     */
    public static function getUserFilter(
        int $a_pool_id
    ): array {
        global $DIC;

        $ilDB = $DIC->database();

        $res = [];

        $sql = "SELECT ud.usr_id,ud.lastname,ud.firstname,ud.login" .
            " FROM usr_data ud " .
            " RIGHT JOIN booking_member m ON (ud.usr_id = m.user_id)" .
            " WHERE ud.usr_id <> " . $ilDB->quote(ANONYMOUS_USER_ID, "integer") .
            " AND m.booking_pool_id = " . $ilDB->quote($a_pool_id, "integer") .
            " ORDER BY ud.lastname,ud.firstname";

        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["usr_id"]] = $row["lastname"] . ", " . $row["firstname"] .
                " (" . $row["login"] . ")";
        }

        return $res;
    }

    /**
     * Returns true if the participant has a reservation for this object.
     */
    protected function isParticipantAssigned(
        int $a_booking_object_id,
        int $a_participant_id
    ): bool {
        return count(ilBookingReservation::getObjectReservationForUser($a_booking_object_id, $a_participant_id)) > 0;
    }
}
