<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBookingParticipant
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilBookingParticipant
{
    protected $lng;
    protected $db;
    protected $participant_id;
    protected $booking_pool_id;
    protected $is_new;

    /**
     * ilBookingParticipant constructor.
     * @param $a_user_id integer
     * @param $a_booking_pool_id integer
     */
    public function __construct($a_user_id, $a_booking_pool_id)
    {
        if (!ilObjUser::_exists($a_user_id) || !ilObjBookingPool::_exists($a_booking_pool_id)) {
            return false;
        }

        global $DIC;
        $this->lng = $DIC->language();
        $this->db = $DIC->database();
        $this->il_user = $DIC->user();

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

    /**
     * Read from DB
     * @return int|bool participant id if found.
     */
    protected function read()
    {
        $query = 'SELECT participant_id FROM booking_member' .
            ' WHERE user_id = ' . $this->db->quote($this->participant_id, 'integer') .
            ' AND booking_pool_id = ' . $this->db->quote($this->booking_pool_id, 'integer');

        $set = $this->db->query($query);
        $row = $this->db->fetchAssoc($set);
        if (empty($row)) {
            return false;
        } else {
            return $row['participant_id'];
        }
    }

    /**
     * Save booking participant in DB
     */
    protected function save()
    {
        $assigner_id = $this->il_user->getId();
        $next_id = $this->db->nextId('booking_member');

        $query = 'INSERT INTO booking_member' .
            ' (participant_id, user_id, booking_pool_id, assigner_user_id)' .
            ' VALUES (' . $this->db->quote($next_id, 'integer') .
            ',' . $this->db->quote($this->participant_id, 'integer') .
            ',' . $this->db->quote($this->booking_pool_id, 'integer') .
            ',' . $this->db->quote($assigner_id, 'integer') . ')';

        $this->db->manipulate($query);
    }

    /**
     * @return bool IF read or created
     */
    public function getIsNew()
    {
        return $this->is_new;
    }

    /**
     * Get participants who can not have a reservation for this booking pool object id.
     *
     * @param integer $a_bp_object_id booking pool object
     * @return array formatted data to display in gui table.
     */
    public static function getAssignableParticipants($a_bp_object_id)
    {
        $booking_object = new ilBookingObject($a_bp_object_id);
        $pool_id = $booking_object->getPoolId();
        $pool = new ilObjBookingPool($pool_id, false);
        $overall_limit = (int) $pool->getOverallLimit();

        $res = array();

        $members = ilBookingReservation::getMembersWithoutReservation($a_bp_object_id);

        foreach ($members as $member_id) {
            //check if the user reached the limit of booking in this booking pool.
            $total_reservations = ilBookingReservation::isBookingPoolLimitReachedByUser($member_id, $pool_id);

            if ($overall_limit == 0 || ($overall_limit > 0 && $total_reservations < $overall_limit)) {
                $user_name = ilObjUser::_lookupName($member_id);
                $name = $user_name['lastname'] . ", " . $user_name['firstname'];
                $index = $a_bp_object_id . "_" . $member_id;

                if (!isset($res[$index])) {
                    $res[$index] = array(
                        "user_id" => $member_id,
                        "object_title" => array($booking_object->getTitle()),
                        "name" => $name
                    );
                } else {
                    if (!in_array($booking_object->getTitle(), $res[$index]['object_title'])) {
                        array_push($res[$index]['object_title'], $booking_object->getTitle());
                    }
                }
            }
        }

        return $res;
    }

    public static function getList($a_booking_pool, array $a_filter = null, $a_object_id = null)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();

        $res = array();

        $query = 'SELECT bm.user_id, bm.booking_pool_id, br.object_id, bo.title, br.status' .
            ' FROM booking_member bm' .
            ' LEFT JOIN booking_reservation br ON (bm.user_id = br.user_id)' .
            ' LEFT JOIN booking_object bo ON (br.object_id = bo.booking_object_id AND bo.pool_id = ' . $ilDB->quote($a_booking_pool, 'integer') . ')';

        $where = array('bm.booking_pool_id =' . $ilDB->quote($a_booking_pool, 'integer'));
        if ($a_object_id) {
            $where[] = 'br.object_id = ' . $ilDB->quote($a_object_id, 'integer');
        }
        if ($a_filter['title']) {
            $where[] = '(' . $ilDB->like('title', 'text', '%' . $a_filter['title'] . '%') .
                ' OR ' . $ilDB->like('description', 'text', '%' . $a_filter['title'] . '%') . ')';
        }
        if ($a_filter['user_id']) {
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

                if ($status != ilBookingReservation::STATUS_CANCELLED && $row['title'] != "") {
                    $res[$index]['object_title'] = array($row['title']);
                    $res[$index]['obj_count'] = 1;
                    $res[$index]['object_ids'][] = $row['object_id'];
                }
            } else {
                if ($row['title'] != "" && (!in_array(
                    $row['title'],
                    $res[$index]['object_title']
                ) && $status != ilBookingReservation::STATUS_CANCELLED)) {
                    array_push($res[$index]['object_title'], $row['title']);
                    $res[$index]['obj_count'] = $res[$index]['obj_count'] + 1;
                    $res[$index]['object_ids'][] = $row['object_id'];
                }
            }
            $res[$index]['user_id'] = $row['user_id'];
        }

        $bp = new ilObjBookingPool($a_booking_pool, false);

        foreach ($res as $index => $val) {
            $actions = [];
            // action assign only if user did not booked all objects.
            //if($res[$index]['obj_count'] < ilBookingObject::getNumberOfObjectsForPool($a_booking_pool))

            // alex: this does not seem to be correct: assignments are always possible for all objects
            $has_schedule = ($bp->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE);
            $limit_reached = (!$has_schedule && $bp->getOverallLimit() <= $val['obj_count']);
            if (!$limit_reached) {
                $ctrl->setParameterByClass('ilbookingparticipantgui', 'bkusr', $val['user_id']);
                $actions[] = array(
                    'text' => $lng->txt("book_assign_object"),
                    'url' => $ctrl->getLinkTargetByClass("ilbookingparticipantgui", 'assignObjects')
                );
                $ctrl->setParameterByClass('ilbookingparticipantgui', 'bkusr', '');
            }
            

            if ($bp->getScheduleType() == ilObjBookingPool::TYPE_NO_SCHEDULE && $val['obj_count'] == 1) {
                $ctrl->setParameterByClass('ilbookingobjectgui', 'bkusr', $val['user_id']);
                $ctrl->setParameterByClass('ilbookingobjectgui', 'object_id', $val['object_ids'][0]);
                $ctrl->setParameterByClass('ilbookingobjectgui', 'part_view', ilBookingParticipantGUI::PARTICIPANT_VIEW);

                $actions[] = array(
                    'text' => $lng->txt("book_deassign"),
                    'url' => $ctrl->getLinkTargetByClass("ilbookingobjectgui", 'rsvConfirmCancelUser')
                );

                $ctrl->setParameterByClass('ilbookingparticipantgui', 'bkusr', '');
                $ctrl->setParameterByClass('ilbookingparticipantgui', 'object_id', '');
                $ctrl->setParameterByClass('ilbookingobjectgui', 'part_view', '');
            } elseif ($bp->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE || $res[$index]['obj_count'] > 1) {
                $ctrl->setParameterByClass('ilobjbookingpoolgui', 'user_id', $val['user_id']);
                $actions[] = array(
                    'text' => $lng->txt("book_deassign"),
                    'url' => $ctrl->getLinkTargetByClass("ilobjbookingpoolgui", 'log')
                );
                $ctrl->setParameterByClass('ilobjbookingpoolgui', 'user_id', '');
            }

            //add the actions
            $res[$index]['actions'] = $actions;
        }
        //echo "<pre>"; print_r($res); exit;
        return $res;
    }

    /**
     * Get all participants for a booking pool.
     * @param $a_booking_pool_id
     * @return array
     */
    public static function getBookingPoolParticipants(integer $a_booking_pool_id) : array
    {
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
     *Get user data from db for an specific pool id.
     *
     * @param integer $a_pool_id
     * @return array
     */
    public static function getUserFilter($a_pool_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $res = array();

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
     * @param $a_booking_object_id
     * @param $a_participant_id
     * @return bool
     */
    protected function isParticipantAssigned($a_booking_object_id, $a_participant_id)
    {
        if (!empty(ilBookingReservation::getObjectReservationForUser($a_booking_object_id, $a_participant_id))) {
            return true;
        } else {
            return false;
        }
    }
}
