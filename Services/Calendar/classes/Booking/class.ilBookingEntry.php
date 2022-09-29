<?php

declare(strict_types=1);
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Booking definition
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesBooking
 */
class ilBookingEntry
{
    protected ilDBInterface $db;
    protected ilObjUser $user;

    private int $id = 0;
    private int $obj_id = 0;

    private int $deadline = 0;
    private int $num_bookings = 1;
    private ?array $target_obj_ids = [];
    private int $booking_group = 0;

    /**
     * Constructor
     */
    public function __construct(int $a_booking_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->user = $DIC->user();
        $this->setId($a_booking_id);
        if ($this->getId()) {
            $this->read();
        }
    }

    /**
     * Reset booking group (in case of deletion)
     */
    public static function resetGroup(int $a_group_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'UPDATE booking_entry SET booking_group = ' . $ilDB->quote(0, 'integer') . ' ' .
            'WHERE booking_group = ' . $ilDB->quote($a_group_id, 'integer');
        $ilDB->manipulate($query);
        return true;
    }

    /**
     * Lookup bookings of user
     * @param int[]
     * @param int
     * @param ?ilDateTime
     * @return int[]
     */
    public static function lookupBookingsOfUser(array $a_app_ids, int $a_usr_id, ?ilDateTime $start = null): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT entry_id FROM booking_user ' .
            'WHERE ' . $ilDB->in('entry_id', $a_app_ids, false, 'integer') . ' ' .
            'AND user_id = ' . $ilDB->quote($a_usr_id, 'integer');

        $res = $ilDB->query($query);

        $booked_entries = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $booked_entries[] = (int) $row->entry_id;
        }
        return $booked_entries;
    }

    protected function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setBookingGroup(int $a_id): void
    {
        $this->booking_group = $a_id;
    }

    public function getBookingGroup(): int
    {
        return $this->booking_group;
    }

    public function setObjId(int $a_id): void
    {
        $this->obj_id = $a_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function setDeadlineHours(int $a_hours): void
    {
        $this->deadline = $a_hours;
    }

    public function getDeadlineHours(): int
    {
        return $this->deadline;
    }

    public function setNumberOfBookings(int $a_num): void
    {
        $this->num_bookings = $a_num;
    }

    public function getNumberOfBookings(): int
    {
        return $this->num_bookings;
    }

    /**
     * @param int[]|null $a_obj_id
     */
    public function setTargetObjIds(?array $a_obj_id): void
    {
        $this->target_obj_ids = $a_obj_id;
    }

    /**
     * @return int[] | null
     */
    public function getTargetObjIds(): ?array
    {
        return $this->target_obj_ids;
    }

    /**
     * Check if target ref id is visible
     */
    public function isTargetObjectVisible(int $a_ref_id): bool
    {
        // no course/group filter
        if (!$this->getTargetObjIds()) {
            return true;
        }

        $obj_id = ilObject::_lookupObjId($a_ref_id);
        return in_array($obj_id, $this->getTargetObjIds());
    }

    public function save(): void
    {
        $this->setId($this->db->nextId('booking_entry'));
        $query = 'INSERT INTO booking_entry (booking_id,obj_id,deadline,num_bookings,booking_group) ' .
            "VALUES ( " .
            $this->db->quote($this->getId(), 'integer') . ', ' .
            $this->db->quote($this->getObjId(), 'integer') . ', ' .
            $this->db->quote($this->getDeadlineHours(), 'integer') . ', ' .
            $this->db->quote($this->getNumberOfBookings(), 'integer') . ',' .
            $this->db->quote($this->getBookingGroup(), 'integer') . ' ' .
            ") ";
        $this->db->manipulate($query);

        foreach ((array) $this->target_obj_ids as $obj_id) {
            $query = 'INSERT INTO booking_obj_assignment (booking_id, target_obj_id) ' .
                'VALUES( ' .
                $this->db->quote($this->getId(), 'integer') . ', ' .
                $this->db->quote($obj_id, 'integer') . ' ' .
                ')';
            $this->db->manipulate($query);
        }
    }

    public function update(): void
    {
        if (!$this->getId()) {
            return;
        }

        $query = "UPDATE booking_entry SET " .
            " obj_id = " . $this->db->quote($this->getObjId(), 'integer') . ", " .
            " deadline = " . $this->db->quote($this->getDeadlineHours(), 'integer') . ", " .
            " num_bookings = " . $this->db->quote($this->getNumberOfBookings(), 'integer') . ', ' .
            'booking_group = ' . $this->db->quote($this->getBookingGroup(), 'integer') . ' ' .
            'WHERE booking_id = ' . $this->db->quote($this->getId(), 'integer');
        $this->db->manipulate($query);

        // obj assignments
        $query = 'DELETE FROM booking_obj_assignment ' .
            'WHERE booking_id = ' . $this->db->quote($this->getId(), 'integer');
        $this->db->manipulate($query);

        foreach ((array) $this->target_obj_ids as $obj_id) {
            $query = 'INSERT INTO booking_obj_assignment (booking_id, target_obj_id) ' .
                'VALUES( ' .
                $this->db->quote($this->getId(), 'integer') . ', ' .
                $this->db->quote($obj_id, 'integer') . ' ' .
                ')';
            $this->db->manipulate($query);
        }
    }

    public function delete(): void
    {
        $query = "DELETE FROM booking_entry " .
            "WHERE booking_id = " . $this->db->quote($this->getId(), 'integer');
        $this->db->manipulate($query);
        $query = 'DELETE FROM booking_obj_assignment ' .
            'WHERE booking_id = ' . $this->db->quote($this->getId(), 'integer');
        $this->db->manipulate($query);
    }

    protected function read(): void
    {
        if (!$this->getId()) {
            return;
        }

        $query = "SELECT * FROM booking_entry " .
            "WHERE booking_id = " . $this->db->quote($this->getId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $this->setObjId((int) $row['obj_id']);
            $this->setDeadlineHours((int) $row['deadline']);
            $this->setNumberOfBookings((int) $row['num_bookings']);
            $this->setBookingGroup((int) $row['booking_group']);
        }

        $query = 'SELECT * FROM booking_obj_assignment ' .
            'WHERE booking_id = ' . $this->db->quote($this->getId(), 'integer');
        $res = $this->db->query($query);
        $this->target_obj_ids = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->target_obj_ids[] = $row->target_obj_id;
        }
    }

    /**
     * check if current (or given) user is entry owner
     * @param int|null $a_user_id
     * @return    bool
     */
    public function isOwner(?int $a_user_id = null): bool
    {
        if (!$a_user_id) {
            $a_user_id = $this->user->getId();
        }
        if ($this->getObjId() == $a_user_id) {
            return true;
        }
        return false;
    }

    /**
     * Remove unused booking entries
     */
    public static function removeObsoleteEntries(): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $set = $ilDB->query('SELECT DISTINCT(context_id) FROM cal_entries e' .
            ' JOIN cal_cat_assignments a ON (e.cal_id = a.cal_id)' .
            ' JOIN cal_categories c ON (a.cat_id = c.cat_id) WHERE c.type = ' . $ilDB->quote(
                ilCalendarCategory::TYPE_CH,
                'integer'
            ));

        $used = array();
        while ($row = $ilDB->fetchAssoc($set)) {
            $used[] = $row['context_id'];
        }
        $ilDB->query($q = 'DELETE FROM booking_entry WHERE ' . $ilDB->in('booking_id', $used, true, 'integer'));
        $ilDB->query($q = 'DELETE FROM booking_obj_assignment WHERE ' . $ilDB->in(
            'booking_id',
            $used,
            true,
            'integer'
        ));
    }

    /**
     * Get instance by calendar entry
     * @param int $id
     * @return ilBookingEntry|null
     */
    public static function getInstanceByCalendarEntryId(int $a_id): ?ilBookingEntry
    {
        $cal_entry = new ilCalendarEntry($a_id);
        $booking_id = $cal_entry->getContextId();
        if ($booking_id) {
            return new self($booking_id);
        }
        return null;
    }

    /**
     * Which objects are bookable?
     * @param int[]    $a_obj_ids
     * @param int|null $a_target_obj_id
     * @return    int[]
     */
    public static function isBookable(array $a_obj_ids, ?int $a_target_obj_id = null): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        if ($a_target_obj_id) {
            $query = 'SELECT DISTINCT(obj_id) FROM booking_entry be ' .
                'JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id ' .
                'WHERE ' . $ilDB->in('obj_id', $a_obj_ids, false, 'integer') . ' ' .
                'AND bo.target_obj_id = ' . $ilDB->quote($a_target_obj_id, 'integer');
        } else {
            $query = 'SELECT DISTINCT(obj_id) FROM booking_entry be ' .
                'WHERE ' . $ilDB->in('obj_id', $a_obj_ids, false, 'integer') . ' ';
        }

        $res = $ilDB->query($query);
        $all = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $all[] = $row->obj_id;
        }
        return $all;
    }

    /**
     * Consultation hours are offered if
     * 1) consultation hour owner is admin or tutor and no object assignment
     * 2) object is assigned to consultation hour
     * @param int[] $a_obj_ids
     * @param int[] $a_user_ids
     * @return int[] user ids
     */
    public static function lookupBookableUsersForObject(array $a_obj_id, array $a_user_ids): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT be.obj_id bobj FROM booking_entry be ' .
            'JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id ' .
            'JOIN cal_entries ce on be.booking_id = ce.context_id ' .
            'JOIN cal_cat_assignments cca on ce.cal_id = cca.cal_id ' .
            'JOIN cal_categories cc on cca.cat_id = cc.cat_id ' .
            'WHERE ' . $ilDB->in('be.obj_id', $a_user_ids, false, 'integer') . ' ' .
            'AND ' . $ilDB->in('bo.target_obj_id', $a_obj_id, false, 'integer') . ' ' .
            'AND cc.obj_id = be.obj_id ' .
            'AND cc.type = ' . $ilDB->quote(ilCalendarCategory::TYPE_CH, 'integer') . ' ';

        $res = $ilDB->query($query);

        $objs = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!in_array($row->bobj, $objs)) {
                $objs[] = (int) $row->bobj;
            }
        }

        // non filtered booking entries
        $query = 'SELECT be.obj_id bobj FROM booking_entry be ' .
            'LEFT JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id ' .
            'JOIN cal_entries ce on be.booking_id = ce.context_id ' .
            'JOIN cal_cat_assignments cca on ce.cal_id = cca.cal_id ' .
            'JOIN cal_categories cc on cca.cat_id = cc.cat_id ' .
            'WHERE bo.booking_id IS NULL ' .
            'AND ' . $ilDB->in('be.obj_id', $a_user_ids, false, 'integer') . ' ' .
            'AND cc.obj_id = be.obj_id ' .
            'AND cc.type = ' . $ilDB->quote(ilCalendarCategory::TYPE_CH, 'integer') . ' ';

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if (!in_array($row->bobj, $objs)) {
                $objs[] = (int) $row->bobj;
            }
        }
        return $objs;
    }

    /**
     * Check if object has assigned consultation hour appointments
     */
    public static function hasObjectBookingEntries(int $a_obj_id, int $a_usr_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $user_restriction = '';
        if ($a_usr_id) {
            $user_restriction = 'AND obj_id = ' . $ilDB->quote($a_usr_id, ilDBConstants::T_INTEGER) . ' ';
        }

        $query = 'SELECT be.booking_id FROM booking_entry be ' .
            'JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id ' .
            'WHERE bo.target_obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
            $user_restriction;

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return true;
        }
        return false;
    }

    public static function lookupBookingMessage(int $a_entry_id, int $a_usr_id): string
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT * from booking_user ' .
            'WHERE entry_id = ' . $ilDB->quote($a_entry_id, 'integer') . ' ' .
            'AND user_id = ' . $ilDB->quote($a_usr_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->booking_message;
        }
        return '';
    }

    /**
     * Write booking message
     */
    public static function writeBookingMessage(int $a_entry_id, int $a_usr_id, string $a_message): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'UPDATE booking_user SET ' .
            'booking_message = ' . $ilDB->quote($a_message, 'text') . ' ' .
            'WHERE entry_id = ' . $ilDB->quote($a_entry_id, 'integer') . ' ' .
            'AND user_id = ' . $ilDB->quote($a_usr_id, 'integer');

        $ilDB->manipulate($query);
    }

    /**
     * get current number of bookings
     */
    public function getCurrentNumberOfBookings(int $a_entry_id): int
    {
        $set = $this->db->query('SELECT COUNT(*) AS counter FROM booking_user' .
            ' WHERE entry_id = ' . $this->db->quote($a_entry_id, 'integer'));
        $row = $this->db->fetchAssoc($set);
        return (int) $row['counter'];
    }

    /**
     * get current bookings
     * @param int $a_entry_id
     * @return    int[]
     */
    public function getCurrentBookings(int $a_entry_id): array
    {
        $set = $this->db->query('SELECT user_id FROM booking_user' .
            ' WHERE entry_id = ' . $this->db->quote($a_entry_id, 'integer'));
        $res = array();
        while ($row = $this->db->fetchAssoc($set)) {
            $res[] = (int) $row['user_id'];
        }
        return $res;
    }

    /**
     * Lookup booked users for appointment
     * @param int $a_app_id
     * @return int[]
     */
    public static function lookupBookingsForAppointment(int $a_app_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT user_id FROM booking_user ' .
            'WHERE entry_id = ' . $ilDB->quote($a_app_id, 'integer');
        $res = $ilDB->query($query);
        $users = [];
        while ($row = $ilDB->fetchObject($res)) {
            $users[] = (int) $row->user_id;
        }
        return $users;
    }

    /**
     * Lookup booking for an object and user
     * @param int $a_obj_id
     * @param int $a_usr_id
     * @return array<int, array<{dt: int, dtend: int, owner: int}>>
     */
    public static function lookupBookingsForObject(int $a_obj_id, int $a_usr_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT bu.user_id, starta, enda FROM booking_user bu ' .
            'JOIN cal_entries ca ON entry_id = ca.cal_id ' .
            'JOIN booking_entry be ON context_id = booking_id ' .
            'JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id ' .
            'WHERE bo.target_obj_id = ' . $ilDB->quote($a_obj_id, 'integer') . ' ' .
            'AND be.obj_id = ' . $ilDB->quote($a_usr_id, ilDBConstants::T_INTEGER) . ' ' .
            'ORDER BY starta';
        $res = $ilDB->query($query);

        $bookings = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $dt = new ilDateTime($row->starta, IL_CAL_DATETIME, ilTimeZone::UTC);
            $dt_end = new ilDateTime($row->enda, IL_CAL_DATETIME, ilTimeZone::UTC);
            $bookings[$row->user_id][] = [
                'dt' => $dt->get(IL_CAL_UNIX),
                'dtend' => $dt_end->get(IL_CAL_UNIX),
                'owner' => $a_usr_id
            ];
        }
        return $bookings;
    }

    /**
     * Lookup bookings for own and managed consultation hours of an object
     * @return array<string, array<{dt: int, dtend: int, owner: int, explanation: string}>>
     */
    public static function lookupManagedBookingsForObject(int $a_obj_id, int $a_usr_id): array
    {
        $bookings = self::lookupBookingsForObject($a_obj_id, $a_usr_id);
        foreach (ilConsultationHourUtils::lookupManagedUsers($a_usr_id) as $managed_user_id) {
            foreach (self::lookupBookingsForObject($a_obj_id, $managed_user_id) as $booked_user => $booking) {
                $fullname = ilObjUser::_lookupFullname($managed_user_id);
                foreach ($booking as $booking_entry) {
                    $booking_entry['explanation'] = '(' . $fullname . ')';
                    $bookings[$booked_user][] = $booking_entry;
                }
            }
        }
        return $bookings;
    }

    /**
     * get current number of bookings
     */
    public function hasBooked(int $a_entry_id, ?int $a_user_id = null): bool
    {
        if (!$a_user_id) {
            $a_user_id = $this->user->getId();
        }

        $query = 'SELECT COUNT(*) AS counter FROM booking_user' .
            ' WHERE entry_id = ' . $this->db->quote($a_entry_id, 'integer') .
            ' AND user_id = ' . $this->db->quote($a_user_id, 'integer');
        $set = $this->db->query($query);
        $row = $this->db->fetchAssoc($set);

        return (bool) $row['counter'];
    }

    /**
     * get current number of bookings
     */
    public function isBookedOut(int $a_entry_id, bool $a_check_current_user = false): bool
    {
        if ($this->getNumberOfBookings() == $this->getCurrentNumberOfBookings($a_entry_id)) {
            // check against current user
            if ($a_check_current_user) {
                if ($this->hasBooked($a_entry_id)) {
                    return false;
                }
                if ($this->user->getId() == $this->getObjId()) {
                    return false;
                }
            }
            return true;
        }

        $deadline = $this->getDeadlineHours();
        if ($deadline) {
            $entry = new ilCalendarEntry($a_entry_id);
            if (time() + ($deadline * 60 * 60) > $entry->getStart()->get(IL_CAL_UNIX)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a calendar appointment is bookable for a specific user
     */
    public function isAppointmentBookableForUser(int $a_app_id, int $a_user_id): bool
    {
        // #12025
        if ($a_user_id == ANONYMOUS_USER_ID) {
            return false;
        }
        // Check max bookings
        if ($this->getNumberOfBookings() <= $this->getCurrentNumberOfBookings($a_app_id)) {
            return false;
        }

        // Check deadline
        $dead_limit = new ilDateTime(time(), IL_CAL_UNIX);
        $dead_limit->increment(IL_CAL_HOUR, $this->getDeadlineHours());

        $entry = new ilCalendarEntry($a_app_id);
        if (ilDateTime::_after($dead_limit, $entry->getStart())) {
            return false;
        }

        // Check group restrictions
        if (!$this->getBookingGroup()) {
            return true;
        }
        $group_apps = ilConsultationHourAppointments::getAppointmentIdsByGroup(
            $this->getObjId(),
            $this->getBookingGroup()
        );

        // Number of bookings in group
        $bookings = self::lookupBookingsOfUser($group_apps, $a_user_id);

        if (count($bookings) >= ilConsultationHourGroups::lookupMaxBookings($this->getBookingGroup())) {
            return false;
        }
        return true;
    }

    /**
     * book calendar entry for user
     */
    public function book(int $a_entry_id, ?int $a_user_id = null): bool
    {
        if (!$a_user_id) {
            $a_user_id = $this->user->getId();
        }

        if (!$this->hasBooked($a_entry_id, $a_user_id)) {
            $this->db->manipulate('INSERT INTO booking_user (entry_id, user_id, tstamp)' .
                ' VALUES (' . $this->db->quote($a_entry_id, 'integer') . ',' .
                $this->db->quote($a_user_id, 'integer') . ',' . $this->db->quote(time(), 'integer') . ')');

            $mail = new ilCalendarMailNotification();
            $mail->setAppointmentId($a_entry_id);
            $mail->setRecipients(array($a_user_id));
            $mail->setType(ilCalendarMailNotification::TYPE_BOOKING_CONFIRMATION);
            $mail->send();
        }
        return true;
    }

    /**
     * cancel calendar booking for user
     */
    public function cancelBooking(int $a_entry_id, ?int $a_user_id = null): bool
    {
        if (!$a_user_id) {
            $a_user_id = $this->user->getId();
        }

        // @todo do not send mails about past consultation hours
        $entry = new ilCalendarEntry($a_entry_id);

        $past = ilDateTime::_before($entry->getStart(), new ilDateTime(time(), IL_CAL_UNIX));
        if ($this->hasBooked($a_entry_id, $a_user_id) && !$past) {
            $mail = new ilCalendarMailNotification();
            $mail->setAppointmentId($a_entry_id);
            $mail->setRecipients(array($a_user_id));
            $mail->setType(ilCalendarMailNotification::TYPE_BOOKING_CANCELLATION);
            $mail->send();
        }
        $this->deleteBooking($a_entry_id, $a_user_id);
        return true;
    }

    /**
     * Delete booking
     */
    public function deleteBooking(int $a_entry_id, int $a_user_id): bool
    {
        $query = 'DELETE FROM booking_user ' .
            'WHERE entry_id = ' . $this->db->quote($a_entry_id, 'integer') . ' ' .
            'AND user_id = ' . $this->db->quote($a_user_id, 'integer');
        $this->db->manipulate($query);
        return true;
    }
}
