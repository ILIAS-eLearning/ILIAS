<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * a booking reservation
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingReservation
{
    /**
     * @var ilDB
     */
    protected $db;

    protected $id;			// int
    protected $object_id;	// int
    protected $user_id;		// int
    protected $from;		// timestamp
    protected $to;			// timestamp
    protected $status;		// status
    protected $group_id;	// int
    protected $assigner_id;	// int

    /**
     * @var int
     */
    protected $context_obj_id = 0;

    const STATUS_IN_USE = 2;
    const STATUS_CANCELLED = 5;

    /**
     * @var ilBookingReservationDBRepository
     */
    protected $repo;

    /**
     * Constructor
     *
     * if id is given will read dataset from db
     *
     * @param	int	$a_id
     */
    public function __construct($a_id = null)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->id = (int) $a_id;

        $f = new ilBookingReservationDBRepositoryFactory();
        $this->repo = $f->getRepo();

        $this->read();
    }

    /**
     * Get id
     * @return	int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set object id
     * @param	int	$a_object_id
     */
    public function setObjectId($a_object_id)
    {
        $this->object_id = $a_object_id;
    }

    /**
     * Get object id
     * @return	int
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * Set booking user id
     * @param	int	$a_user_id
     */
    public function setUserId($a_user_id)
    {
        $this->user_id = (int) $a_user_id;
    }

    /**
     * Get booking user id
     * @return	int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set assigner user id
     * @param $a_assigner_id
     */
    public function setAssignerId($a_assigner_id)
    {
        $this->assigner_id = (int) $a_assigner_id;
    }

    /**
     * Get assigner user id
     * @return int
     */
    public function getAssignerId()
    {
        return $this->assigner_id;
    }

    /**
     * Set booking from date
     * @param	int	$a_from
     */
    public function setFrom($a_from)
    {
        $this->from = (int) $a_from;
    }

    /**
     * Get booking from date
     * @return	int
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set booking to date
     * @param	int	$a_to
     */
    public function setTo($a_to)
    {
        $this->to = (int) $a_to;
    }

    /**
     * Get booking to date
     * @return	int
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Set booking status
     * @param	int	$a_status
     */
    public function setStatus($a_status)
    {
        if ($a_status === null) {
            $this->status = null;
        }
        if ($this->isValidStatus((int) $a_status)) {
            $this->status = (int) $a_status;
        }
    }

    /**
     * Get booking status
     * @return	int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Check if given status is valid
     * @param	int	$a_status
     * @return	bool
     */
    public static function isValidStatus($a_status)
    {
        if (in_array($a_status, array(self::STATUS_IN_USE, self::STATUS_CANCELLED))) {
            return true;
        }
        return false;
    }
    
    /**
     * Set group id
     * @param	int	$a_group_id
     */
    public function setGroupId($a_group_id)
    {
        $this->group_id = $a_group_id;
    }

    /**
     * Get group id
     * @return	int
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * Set context object id
     *
     * @param int $a_val context object id (e.g. course id)
     */
    public function setContextObjId($a_val)
    {
        $this->context_obj_id = $a_val;
    }
    
    /**
     * Get context object id
     *
     * @return int context object id (e.g. course id)
     */
    public function getContextObjId()
    {
        return $this->context_obj_id;
    }
    
    
    /**
     * Get dataset from db
     */
    protected function read()
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

    /**
     * Create new entry in db
     * @return bool
     */
    public function save()
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

    /**
     * Update entry in db
     */
    public function update()
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

    /**
     * Delete single entry
     */
    public function delete()
    {
        $this->repo->delete($this->id);
    }
    

    /**
     * Check if any of given objects are bookable
     * @param	array	$a_ids
     * @param	int		$a_from
     * @param	int		$a_to
     * @param	int		$a_return_single
     * @return	int
     */
    public static function getAvailableObject(array $a_ids, $a_from, $a_to, $a_return_single = true, $a_return_counter = false)
    {
        $nr_map = ilBookingObject::getNrOfItemsForObjects($a_ids);

        $f = new ilBookingReservationDBRepositoryFactory();
        $repo = $f->getRepo();

        $blocked = $counter = array();
        foreach ($repo->getNumberOfReservations($a_ids, $a_from, $a_to) as $row) {
            if ($row['cnt'] >= $nr_map[$row['object_id']]) {
                $blocked[] = $row['object_id'];
            } elseif ($a_return_counter) {
                $counter[$row['object_id']] = (int) $nr_map[$row['object_id']] - (int) $row['cnt'];
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
        if (sizeof($available)) {
            if ($a_return_counter) {
                foreach ($a_ids as $id) {
                    if (!isset($counter[$id])) {
                        $counter[$id] = (int) $nr_map[$id];
                    }
                }
                return $counter;
            } elseif ($a_return_single) {
                return array_shift($available);
            } else {
                return $available;
            }
        }
    }
    
    public static function isObjectAvailableInPeriod($a_obj_id, ilBookingSchedule $a_schedule, $a_from, $a_to)
    {
        global $DIC;

        $ilDB = $DIC->database();
            
        if (!$a_from) {
            $a_from = time();
        }
        if (!$a_to) {
            $a_to = strtotime("+1year", $a_from);
        }
        
        if ($a_from > $a_to) {
            return;
        }

        // all nr of reservations in period that are not over yet (to >= now)
        $f = new ilBookingReservationDBRepositoryFactory();
        $repo = $f->getRepo();
        $res = $repo->getNumberOfReservations([$a_obj_id], $a_from, $a_to, true);
        $booked_in_period = (int) $res[$a_obj_id]["cnt"];

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
            $day_slots = $schedule_slots[date("w", $a_from)];
            if ($day_slots) {
                foreach ($day_slots as $slot) {
                    // convert slot to current datetime
                    $slot = explode("-", $slot);
                    $slot_from = strtotime(date("Y-m-d", $a_from) . " " . $slot[0]);
                    $slot_to = strtotime(date("Y-m-d", $a_from) . " " . $slot[1]);
                    // slot has to be in the future and part of schedule availability
                    if ($slot_to > time() &&
                        $slot_from >= $av_from &&
                        ($slot_to <= $av_to || is_null($av_to))) {
                        $available_in_period += $per_slot;
                    }
                }
            }
            
            $a_from += (60 * 60 * 24);
        }
        if ($available_in_period - $booked_in_period > 0) {
            return true;
        }

        return false;
    }

    //check if the user reached the limit of bookings in this booking pool.
    public static function isBookingPoolLimitReachedByUser(int $a_user_id, int $a_pool_id) : int
    {
        global $DIC;
        $ilDB = $DIC->database();

        $booking_pool_objects = ilBookingObject::getObjectsForPool($a_pool_id);

        $query = "SELECT count(user_id) total" .
            " FROM booking_reservation" .
            " WHERE " . $ilDB->in('object_id', $booking_pool_objects, false, 'integer') .
            " AND user_id = " . $a_user_id .
            " AND (status IS NULL OR status <> " . ilBookingReservation::STATUS_CANCELLED . ')';
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC);

        return (int) $row['total'];
    }

    public static function getMembersWithoutReservation(int $a_object_id) : array
    {
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
            ' AND (status IS NULL OR status <> ' . ilBookingReservation::STATUS_CANCELLED . '))';

        $set = $ilDB->query($query);

        while ($row = $ilDB->fetchAssoc($set)) {
            $res[] = $row['user_id'];
        }

        return $res;
    }
    
    public static function isObjectAvailableNoSchedule($a_obj_id)
    {
        $available = self::getNumAvailablesNoSchedule($a_obj_id);
        return (bool) $available; // #11864
    }
    public static function numAvailableFromObjectNoSchedule($a_obj_id)
    {
        $available = self::getNumAvailablesNoSchedule($a_obj_id);
        return (int) $available;
    }

    public static function getNumAvailablesNoSchedule($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $all = ilBookingObject::getNrOfItemsForObjects(array($a_obj_id));
        $all = (int) $all[$a_obj_id];

        $set = $ilDB->query('SELECT COUNT(*) cnt' .
            ' FROM booking_reservation r' .
            ' JOIN booking_object o ON (o.booking_object_id = r.object_id)' .
            ' WHERE (status IS NULL OR status <> ' . $ilDB->quote(self::STATUS_CANCELLED, 'integer') . ')' .
            ' AND r.object_id = ' . $ilDB->quote($a_obj_id, 'integer'));
        $cnt = $ilDB->fetchAssoc($set);
        $cnt = (int) $cnt['cnt'];

        return (int) $all - $cnt; // #11864
    }

    /**
     * Get details about object reservation
     * @param	int	$a_object_id
     * @return	array
     */
    public static function getCurrentOrUpcomingReservation($a_object_id)
    {
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
        $row = $ilDB->fetchAssoc($set);
        return $row;
    }
    
    public static function getObjectReservationForUser($a_object_id, $a_user_id, $a_multi = false)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query('SELECT booking_reservation_id FROM booking_reservation' .
            ' WHERE user_id = ' . $ilDB->quote($a_user_id, 'integer') .
            ' AND object_id = ' . $ilDB->quote($a_object_id, 'integer') .
            ' AND (status <> ' . $ilDB->quote(self::STATUS_CANCELLED, 'integer') .
            ' OR STATUS IS NULL)');
        if (!$a_multi) {
            $row = $ilDB->fetchAssoc($set);
            return $row['booking_reservation_id'];
        } else {
            $res = array();
            while ($row = $ilDB->fetchAssoc($set)) {
                $res[] = $row['booking_reservation_id'];
            }
            return $res;
        }
    }

    /**
     * List all reservations
     * @param	array	$a_object_ids
     * @param	int		$a_limit
     * @param	int		$a_offset
     * @param	array	$a_offset
     * @return	array
     */
    public static function getList($a_object_ids, $a_limit = 10, $a_offset = 0, array $filter = [])
    {
        global $DIC;

        $ilDB = $DIC->database();

        $sql = 'SELECT r.*,o.title' .
            ' FROM booking_reservation r' .
            ' JOIN booking_object o ON (o.booking_object_id = r.object_id)';

        $count_sql = 'SELECT COUNT(*) AS counter' .
            ' FROM booking_reservation r' .
            ' JOIN booking_object o ON (o.booking_object_id = r.object_id)';
        
        $where = array($ilDB->in('r.object_id', $a_object_ids, '', 'integer'));
        if ($filter['status']) {
            if ($filter['status'] > 0) {
                $where[] = 'status = ' . $ilDB->quote($filter['status'], 'integer');
            } else {
                $where[] = '(status != ' . $ilDB->quote(-$filter['status'], 'integer') .
                    ' OR status IS NULL)';
            }
        }
        if ($filter['from']) {
            $where[] = 'date_from >= ' . $ilDB->quote($filter['from'], 'integer');
        }
        if ($filter['to']) {
            $where[] = 'date_to <= ' . $ilDB->quote($filter['to'], 'integer');
        }
        if (sizeof($where)) {
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
     * @param array $a_object_ids
     * @return array
     */
    public static function getUserFilter(array $a_object_ids)
    {
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
     * @param	array	$a_ids
     * @param	int		$a_status
     * @return	bool
     */
    public static function changeStatus(array $a_ids, $a_status)
    {
        global $DIC;

        $ilDB = $DIC->database();

        if (self::isValidStatus($a_status)) {
            return $ilDB->manipulate('UPDATE booking_reservation' .
                ' SET status = ' . $ilDB->quote($a_status, 'integer') .
                ' WHERE ' . $ilDB->in('booking_reservation_id', $a_ids, '', 'integer'));
        }
    }
    
    public function getCalendarEntry()
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
        return $row["cal_id"];
    }
    
    /**
     * Get reservation ids from aggregated id for cancellation
     *
     * @param int $a_obj_id
     * @param int $a_user_id
     * @param int $a_from
     * @param int $a_to
     * @return array
     */
    public static function getCancelDetails($a_obj_id, $a_user_id, $a_from, $a_to)
    {
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
            $res[] = $row["booking_reservation_id"];
        }
        
        return $res;
    }
}
