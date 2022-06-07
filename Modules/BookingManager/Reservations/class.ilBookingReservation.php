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
 * a booking reservation
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBookingReservation
{
    public const STATUS_IN_USE = 2;
    public const STATUS_CANCELLED = 5;

    protected ilDBInterface $db;
    protected int $id = 0;
    protected int $object_id = 0;
    protected int $user_id = 0;
    protected int $from = 0;
    protected int $to = 0;
    protected int $status = 0;
    protected int $group_id = 0;
    protected int $assigner_id = 0;
    protected int $context_obj_id = 0;
    protected ilBookingReservationDBRepository $repo;

    public function __construct(
        int $a_id = null
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = (int) $a_id;

        $f = new ilBookingReservationDBRepositoryFactory();
        $this->repo = $f->getRepo();

        $this->read();
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function setObjectId(int $a_object_id) : void
    {
        $this->object_id = $a_object_id;
    }

    public function getObjectId() : int
    {
        return $this->object_id;
    }

    public function setUserId(int $a_user_id) : void
    {
        $this->user_id = $a_user_id;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    /**
     * Set assigner user id
     */
    public function setAssignerId(int $a_assigner_id) : void
    {
        $this->assigner_id = $a_assigner_id;
    }

    public function getAssignerId() : int
    {
        return $this->assigner_id;
    }

    /**
     * Set booking from date
     */
    public function setFrom(int $a_from) : void
    {
        $this->from = $a_from;
    }

    public function getFrom() : int
    {
        return $this->from;
    }

    /**
     * Set booking to date
     */
    public function setTo(int $a_to) : void
    {
        $this->to = $a_to;
    }

    public function getTo() : int
    {
        return $this->to;
    }

    /**
     * Set booking status
     */
    public function setStatus(?int $a_status) : void
    {
        if ($a_status === null) {
            $this->status = null;
        }
        if (self::isValidStatus((int) $a_status)) {
            $this->status = (int) $a_status;
        }
    }

    /**
     * Get booking status
     */
    public function getStatus() : ?int
    {
        return $this->status;
    }

    public static function isValidStatus(int $a_status) : bool
    {
        return in_array($a_status, array(self::STATUS_IN_USE, self::STATUS_CANCELLED));
    }
    
    public function setGroupId(int $a_group_id) : void
    {
        $this->group_id = $a_group_id;
    }

    public function getGroupId() : int
    {
        return $this->group_id;
    }

    /**
     * @param int $a_val context object id (e.g. course id)
     */
    public function setContextObjId(int $a_val) : void
    {
        $this->context_obj_id = $a_val;
    }
    
    /**
     * @return int context object id (e.g. course id)
     */
    public function getContextObjId() : int
    {
        return $this->context_obj_id;
    }
    
    protected function read() : void
    {
        if ($this->id) {
            $row = $this->repo->getForId($this->id);
            $this->setUserId($row['user_id']);
            $this->setAssignerId($row['assigner_id']);
            $this->setObjectId($row['object_id']);
            $this->setFrom($row['date_from']);
            $this->setTo($row['date_to']);
            $this->setStatus($row['status']);
            $this->setGroupId($row['group_id']);
            $this->setContextObjId($row['context_obj_id']);
        }
    }

    public function save() : bool
    {
        if ($this->id) {
            return false;
        }

        $this->id = $this->repo->create(
            $this->getUserId(),
            $this->getAssignerId(),
            $this->getObjectId(),
            $this->getContextObjId(),
            $this->getFrom(),
            $this->getTo(),
            $this->getStatus(),
            $this->getGroupId()
        );
        return ($this->id > 0);
    }

    public function update() : bool
    {
        if (!$this->id) {
            return false;
        }

        $this->repo->update(
            $this->id,
            $this->getUserId(),
            $this->getAssignerId(),
            $this->getObjectId(),
            $this->getContextObjId(),
            $this->getFrom(),
            $this->getTo(),
            $this->getStatus(),
            $this->getGroupId()
        );
        return true;
    }

    public function delete() : void
    {
        $this->repo->delete($this->id);
    }
    

    /**
     * Check if any of given objects are bookable
     */
    public static function getAvailableObject(
        array $a_ids,
        int $a_from,
        int $a_to,
        bool $a_return_single = true,
        bool $a_return_counter = false
    ) : array {
        $nr_map = ilBookingObject::getNrOfItemsForObjects($a_ids);

        $f = new ilBookingReservationDBRepositoryFactory();
        $repo = $f->getRepo();

        $blocked = $counter = array();
        foreach ($repo->getNumberOfReservations($a_ids, $a_from, $a_to) as $row) {
            if ($row['cnt'] >= $nr_map[$row['object_id']]) {
                $blocked[] = $row['object_id'];
            } elseif ($a_return_counter) {
                $counter[$row['object_id']] = $nr_map[$row['object_id']] - (int) $row['cnt'];
            }
        }
        
        // #17868 - validate against schedule availability
        foreach ($a_ids as $obj_id) {
            $bobj = new ilBookingObject($obj_id);
            if ($bobj->getScheduleId()) {
                $schedule = new ilBookingSchedule($bobj->getScheduleId());

                $av_from = ($schedule->getAvailabilityFrom() && !$schedule->getAvailabilityFrom()->isNull())
                    ? $schedule->getAvailabilityFrom()->get(IL_CAL_UNIX)
                    : null;
                $av_to = ($schedule->getAvailabilityTo() && !$schedule->getAvailabilityTo()->isNull())
                    ? strtotime($schedule->getAvailabilityTo()->get(IL_CAL_DATE) . " 23:59:59")
                    : null;
                
                if (($av_from && $a_from < $av_from) ||
                    ($av_to && $a_to > $av_to)) {
                    $blocked[] = $obj_id;
                    unset($counter[$obj_id]);
                }
            }
        }
        
        $available = array_diff($a_ids, $blocked);
        if (count($available)) {
            if ($a_return_counter) {
                foreach ($a_ids as $id) {
                    if (!isset($counter[$id])) {
                        $counter[$id] = $nr_map[$id];
                    }
                }
                return $counter;
            }
            if ($a_return_single) {
                return array_shift($available);
            }
            return $available;
        }
        return [];
    }
    
    public static function isObjectAvailableInPeriod(
        int $a_obj_id,
        ilBookingSchedule $a_schedule,
        int $a_from,
        int $a_to
    ) : bool {
        if (!$a_from) {
            $a_from = time();
        }
        if (!$a_to) {
            $a_to = strtotime("+1year", $a_from);
        }
        
        if ($a_from > $a_to) {
            return false;
        }

        // all nr of reservations in period that are not over yet (to >= now)
        $f = new ilBookingReservationDBRepositoryFactory();
        $repo = $f->getRepo();
        $res = $repo->getNumberOfReservations([$a_obj_id], $a_from, $a_to, true);
        $booked_in_period = (int) ($res[$a_obj_id]["cnt"] ?? 0);

        $per_slot = ilBookingObject::getNrOfItemsForObjects(array($a_obj_id));
        $per_slot = $per_slot[$a_obj_id];
                
        // max available nr of items per (week)day
        $schedule_slots = array();
        $definition = $a_schedule->getDefinition();
        $map = array_flip(array("su", "mo", "tu", "we", "th", "fr", "sa"));
        foreach ($definition as $day => $day_slots) {
            $schedule_slots[$map[$day]] = $day_slots;
        }
        
        $av_from = ($a_schedule->getAvailabilityFrom() && !$a_schedule->getAvailabilityFrom()->isNull())
            ? $a_schedule->getAvailabilityFrom()->get(IL_CAL_UNIX)
            : null;
        $av_to = ($a_schedule->getAvailabilityTo() && !$a_schedule->getAvailabilityTo()->isNull())
            ? strtotime($a_schedule->getAvailabilityTo()->get(IL_CAL_DATE) . " 23:59:59")
            : null;
        
        // sum up max available (to >= now) items in period per (week)day
        $available_in_period = 0;
        $loop = 0;
        while ($a_from < $a_to &&
            ++$loop < 1000) {
            // any slots for current weekday?
            $day_slots = $schedule_slots[date("w", $a_from)] ?? false;
            if ($day_slots) {
                foreach ($day_slots as $slot) {
                    // convert slot to current datetime
                    $slot = explode("-", $slot);
                    $slot_from = strtotime(date("Y-m-d", $a_from) . " " . $slot[0]);
                    $slot_to = strtotime(date("Y-m-d", $a_from) . " " . $slot[1]);
                    // slot has to be in the future and part of schedule availability
                    if ($slot_from >= $av_from &&
                        ($slot_to <= $av_to || is_null($av_to)) &&
                        $slot_to > time()) {
                        $available_in_period += $per_slot;
                    }
                }
            }
            
            $a_from += (60 * 60 * 24);
        }
        return $available_in_period - $booked_in_period > 0;
    }

    //check if the user reached the limit of bookings in this booking pool.
    public static function isBookingPoolLimitReachedByUser(
        int $a_user_id,
        int $a_pool_id
    ) : int {
        global $DIC;
        $ilDB = $DIC->database();

        $booking_pool_objects = ilBookingObject::getObjectsForPool($a_pool_id);

        $query = "SELECT count(user_id) total" .
            " FROM booking_reservation" .
            " WHERE " . $ilDB->in('object_id', $booking_pool_objects, false, 'integer') .
            " AND user_id = " . $a_user_id .
            " AND (status IS NULL OR status <> " . self::STATUS_CANCELLED . ')';
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return (int) $row['total'];
    }

    /**
     * @return int[]
     */
    public static function getMembersWithoutReservation(
        int $a_object_id
    ) : array {
        global $DIC;
        $ilDB = $DIC->database();

        $pool_id = ilBookingObject::lookupPoolId($a_object_id);

        $res = array();
        $query = 'SELECT DISTINCT bm.user_id user_id' .
            ' FROM booking_member bm' .
            ' WHERE bm.booking_pool_id = ' . $ilDB->quote($pool_id, 'integer') .
            ' AND bm.user_id NOT IN (' .
            'SELECT user_id' .
            ' FROM booking_reservation' .
            ' WHERE object_id = ' . $ilDB->quote($a_object_id, 'integer') .
            ' AND (status IS NULL OR status <> ' . self::STATUS_CANCELLED . '))';

        $set = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = (int) $row['user_id'];
        }

        return $res;
    }
    
    public static function isObjectAvailableNoSchedule(int $a_obj_id) : bool
    {
        $available = self::getNumAvailablesNoSchedule($a_obj_id);
        return (bool) $available; // #11864
    }

    public static function numAvailableFromObjectNoSchedule(int $a_obj_id) : int
    {
        return self::getNumAvailablesNoSchedule($a_obj_id);
    }

    public static function getNumAvailablesNoSchedule(int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();

        $all = ilBookingObject::getNrOfItemsForObjects(array($a_obj_id));
        $all = $all[$a_obj_id];

        $set = $ilDB->query('SELECT COUNT(*) cnt' .
            ' FROM booking_reservation r' .
            ' JOIN booking_object o ON (o.booking_object_id = r.object_id)' .
            ' WHERE (status IS NULL OR status <> ' . $ilDB->quote(self::STATUS_CANCELLED, 'integer') . ')' .
            ' AND r.object_id = ' . $ilDB->quote($a_obj_id, 'integer'));
        $cnt = $ilDB->fetchAssoc($set);
        $cnt = (int) $cnt['cnt'];

        return $all - $cnt; // #11864
    }

    /**
     * Get details about object reservation
     */
    public static function getCurrentOrUpcomingReservation(
        int $a_object_id
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $now = $ilDB->quote(time(), 'integer');

        $ilDB->setLimit(1);
        $set = $ilDB->query('SELECT user_id, status, date_from, date_to' .
            ' FROM booking_reservation' .
            ' WHERE ((date_from <= ' . $now . ' AND date_to >= ' . $now . ')' .
            ' OR date_from > ' . $now . ')' .
            ' AND (status <> ' . $ilDB->quote(self::STATUS_CANCELLED, 'integer') .
            ' OR STATUS IS NULL) AND object_id = ' . $ilDB->quote($a_object_id, 'integer') .
            ' ORDER BY date_from');
        return $ilDB->fetchAssoc($set);
    }

    /**
     * @return int[] booking reservation ids
     */
    public static function getObjectReservationForUser(
        int $a_object_id,
        int $a_user_id
    ) : ?array {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query('SELECT booking_reservation_id FROM booking_reservation' .
            ' WHERE user_id = ' . $ilDB->quote($a_user_id, 'integer') .
            ' AND object_id = ' . $ilDB->quote($a_object_id, 'integer') .
            ' AND (status <> ' . $ilDB->quote(self::STATUS_CANCELLED, 'integer') .
            ' OR STATUS IS NULL)');
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = (int) $row['booking_reservation_id'];
        }
        return $res;
    }

    /**
     * List all reservations
     * @return array<string, array>
     */
    public static function getList(
        array $a_object_ids,
        int $a_limit = 10,
        int $a_offset = 0,
        array $filter = []
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        $sql = 'SELECT r.*,o.title' .
            ' FROM booking_reservation r' .
            ' JOIN booking_object o ON (o.booking_object_id = r.object_id)';

        $count_sql = 'SELECT COUNT(*) AS counter' .
            ' FROM booking_reservation r' .
            ' JOIN booking_object o ON (o.booking_object_id = r.object_id)';
        
        $where = array($ilDB->in('r.object_id', $a_object_ids, '', 'integer'));
        if (isset($filter['status'])) {
            if ($filter['status'] > 0) {
                $where[] = 'status = ' . $ilDB->quote($filter['status'], 'integer');
            } else {
                $where[] = '(status != ' . $ilDB->quote(-$filter['status'], 'integer') .
                    ' OR status IS NULL)';
            }
        }
        if (isset($filter['from'])) {
            $where[] = 'date_from >= ' . $ilDB->quote($filter['from'], 'integer');
        }
        if (isset($filter['to'])) {
            $where[] = 'date_to <= ' . $ilDB->quote($filter['to'], 'integer');
        }
        if (count($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
            $count_sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $set = $ilDB->query($count_sql);
        $row = $ilDB->fetchAssoc($set);
        $counter = $row['counter'];

        $sql .= ' ORDER BY date_from DESC, booking_reservation_id DESC';
        
        $ilDB->setLimit($a_limit, $a_offset);
        $set = $ilDB->query($sql);
        $res = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row;
        }

        return array('data' => $res, 'counter' => $counter);
    }
    

    /**
     * Get all users who have reservations for object(s)
     *
     * @param int[] $a_object_ids
     * @return array<int,string> user id => user name
     */
    public static function getUserFilter(
        array $a_object_ids
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $sql = "SELECT ud.usr_id,ud.lastname,ud.firstname,ud.login" .
            " FROM usr_data ud " .
            " LEFT JOIN booking_reservation r ON (r.user_id = ud.usr_id)" .
            " WHERE ud.usr_id <> " . $ilDB->quote(ANONYMOUS_USER_ID, "integer") .
            " AND " . $ilDB->in("r.object_id", $a_object_ids, "", "integer") .
            " ORDER BY ud.lastname,ud.firstname";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[$row["usr_id"]] = $row["lastname"] . ", " . $row["firstname"] .
                " (" . $row["login"] . ")";
        }
                
        return $res;
    }
    

    /**
     * Batch update reservation status
     * @param int[] $a_ids reservation ids
     * @param int $a_status
     * @return int number of changed entries
     */
    public static function changeStatus(
        array $a_ids,
        int $a_status
    ) : int {
        global $DIC;

        $ilDB = $DIC->database();

        if (self::isValidStatus($a_status)) {
            return $ilDB->manipulate('UPDATE booking_reservation' .
                ' SET status = ' . $ilDB->quote($a_status, 'integer') .
                ' WHERE ' . $ilDB->in('booking_reservation_id', $a_ids, '', 'integer'));
        }
        return 0;
    }

    // get calendar id of reservation
    public function getCalendarEntry() : int
    {
        $ilDB = $this->db;

        $set = $ilDB->query("SELECT ce.cal_id FROM cal_entries ce" .
            " JOIN cal_cat_assignments cca ON ce.cal_id = cca.cal_id" .
            " JOIN cal_categories cc ON cca.cat_id = cc.cat_id" .
            " JOIN booking_reservation br ON ce.context_id  = br.booking_reservation_id" .
            " WHERE cc.obj_id = " . $ilDB->quote($this->getUserId(), 'integer') .
            " AND br.user_id = " . $ilDB->quote($this->getUserId(), 'integer') .
            " AND cc.type = " . $ilDB->quote(ilCalendarCategory::TYPE_BOOK, 'integer') .
            " AND ce.context_id = " . $ilDB->quote($this->getId(), 'integer'));
        $row = $ilDB->fetchAssoc($set);
        return (int) $row["cal_id"];
    }
    
    /**
     * Get reservation ids from aggregated id for cancellation
     * @return int[]
     */
    public static function getCancelDetails(
        int $a_obj_id,
        int $a_user_id,
        int $a_from,
        int $a_to
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        
        $res = array();
        
        $sql = "SELECT booking_reservation_id" .
            " FROM booking_reservation" .
            " WHERE object_id = " . $ilDB->quote($a_obj_id, "integer") .
            " AND user_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND date_from = " . $ilDB->quote($a_from, "integer") .
            " AND date_to = " . $ilDB->quote($a_to, "integer") .
            " AND (status IS NULL" .
            " OR status <> " . $ilDB->quote(self::STATUS_CANCELLED, "integer") . ")";
        $set = $ilDB->query($sql);
        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = (int) $row["booking_reservation_id"];
        }
        
        return $res;
    }
}
