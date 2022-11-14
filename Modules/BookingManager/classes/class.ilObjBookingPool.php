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
 * Class ilObjBookingPool
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjBookingPool extends ilObject
{
    public const TYPE_FIX_SCHEDULE = 1;
    public const TYPE_NO_SCHEDULE = 2;
    public const TYPE_NO_SCHEDULE_PREFERENCES = 3;
    protected \ILIAS\BookingManager\InternalDomainService $domain;

    protected bool $offline = true;
    protected bool $public_log = false;
    protected int $schedule_type = 0;
    protected ?int $overall_limit = null;
    protected ?int $reservation_period = null;
    protected int $reminder_status = 0;
    protected int $reminder_day = 1;
    protected int $pref_deadline = 0;
    protected int $preference_nr = 0;


    public function __construct(
        int $a_id = 0,
        bool $a_call_by_reference = true
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $this->type = "book";
        $this->setScheduleType(self::TYPE_FIX_SCHEDULE);
        parent::__construct($a_id, $a_call_by_reference);
        $this->domain = $DIC->bookingManager()->internal()->domain();
    }

    /**
     * Parse properties for sql statements
     */
    protected function getDBFields(): array
    {
        return array(
            "schedule_type" => array("integer", $this->getScheduleType()),
            "pool_offline" => array("integer", $this->isOffline()),
            "public_log" => array("integer", $this->hasPublicLog()),
            "ovlimit" => array("integer", $this->getOverallLimit()),
            "reminder_status" => array("integer", $this->getReminderStatus()),
            "reminder_day" => array("integer", $this->getReminderDay()),
            "rsv_filter_period" => array("integer", $this->getReservationFilterPeriod()),
            "preference_nr" => array("integer", $this->getPreferenceNumber()),
            "pref_deadline" => array("integer", $this->getPreferenceDeadline())
        );
    }

    public function create(): int
    {
        $ilDB = $this->db;

        $new_id = parent::create();

        $fields = $this->getDBFields();
        $fields["booking_pool_id"] = array("integer", $new_id);

        $ilDB->insert("booking_settings", $fields);

        return $new_id;
    }

    public function update(): bool
    {
        $ilDB = $this->db;

        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff
        if ($this->getId()) {
            $ilDB->update(
                "booking_settings",
                $this->getDBFields(),
                array("booking_pool_id" => array("integer", $this->getId()))
            );
        }

        return true;
    }

    public function read(): void
    {
        $ilDB = $this->db;

        parent::read();

        // put here object specific stuff
        if ($this->getId()) {
            $set = $ilDB->query('SELECT * FROM booking_settings' .
                ' WHERE booking_pool_id = ' . $ilDB->quote($this->getId(), 'integer'));
            $row = $ilDB->fetchAssoc($set);
            $this->setOffline($row['pool_offline']);
            $this->setPublicLog($row['public_log']);
            $this->setScheduleType($row['schedule_type']);
            $this->setOverallLimit($row['ovlimit']);
            $this->setReminderStatus($row['reminder_status']);
            $this->setReminderDay($row['reminder_day']);
            $this->setReservationFilterPeriod($row['rsv_filter_period']);
            $this->setPreferenceNumber($row['preference_nr']);
            $this->setPreferenceDeadline($row['pref_deadline']);
        }
    }

    /**
     * Get pools with reminders
     */
    public static function getPoolsWithReminders(): array
    {
        global $DIC;

        $db = $DIC->database();
        $pools = [];
        $set = $db->queryF(
            "SELECT * FROM booking_settings " .
            " WHERE reminder_status = %s " .
            " AND reminder_day > %s " .
            " AND pool_offline = %s ",
            array("integer","integer","integer"),
            array(1,0,0)
        );
        while ($rec = $db->fetchAssoc($set)) {
            $pools[] = $rec;
        }
        return $pools;
    }

    /**
     * @param int $a_obj_id pool id
     * @param int $a_ts timestamp
     */
    public static function writeLastReminderTimestamp(
        int $a_obj_id,
        int $a_ts
    ): void {
        global $DIC;
        $db = $DIC->database();
        $db->update("booking_settings", array(
                "last_remind_ts" => array("integer", $a_ts)
            ), array(	// where
                "booking_pool_id" => array("integer", $a_obj_id)
            ));
    }

    public function delete(): bool
    {
        $ilDB = $this->db;

        $id = $this->getId();

        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }

        // put here your module specific stuff

        $ilDB->manipulate('DELETE FROM booking_settings' .
                ' WHERE booking_pool_id = ' . $ilDB->quote($id, 'integer'));

        $ilDB->manipulate('DELETE FROM booking_schedule' .
                ' WHERE pool_id = ' . $ilDB->quote($id, 'integer'));

        $objects = array();
        $set = $ilDB->query('SELECT booking_object_id FROM booking_object' .
            ' WHERE pool_id = ' . $ilDB->quote($id, 'integer'));
        while ($row = $ilDB->fetchAssoc($set)) {
            $objects[] = $row['booking_object_id'];
        }

        if (count($objects)) {
            $ilDB->manipulate('DELETE FROM booking_reservation' .
                    ' WHERE ' . $ilDB->in('object_id', $objects, '', 'integer'));
        }

        $ilDB->manipulate('DELETE FROM booking_object' .
            ' WHERE pool_id = ' . $ilDB->quote($id, 'integer'));

        return true;
    }

    public function cloneObject(int $target_id, int $copy_id = 0, bool $omit_tree = false): ?ilObject
    {
        $new_obj = parent::cloneObject($target_id, $copy_id, $omit_tree);

        $schedule_manager = $this->domain->schedules($this->getId());

        if ($new_obj !== null) {
            //copy online status if object is not the root copy object
            $cp_options = ilCopyWizardOptions::_getInstance($copy_id);

            if (!$cp_options->isRootNode($this->getRefId())) {
                $new_obj->setOffline($this->isOffline());
            }

            $new_obj->setScheduleType($this->getScheduleType());
            $new_obj->setPublicLog($this->hasPublicLog());
            $new_obj->setOverallLimit($this->getOverallLimit());
            $new_obj->setReminderStatus($this->getReminderStatus());
            $new_obj->setReminderDay($this->getReminderDay());
            $new_obj->setPreferenceNumber($this->getPreferenceNumber());
            $new_obj->setPreferenceDeadline($this->getPreferenceDeadline());

            $smap = null;
            if ($this->getScheduleType() === self::TYPE_FIX_SCHEDULE) {
                // schedules
                foreach ($schedule_manager->getScheduleList() as $schedule_id => $title) {
                    $schedule = new ilBookingSchedule($schedule_id);
                    $smap[$schedule_id] = $schedule->doClone($new_obj->getId());
                }
            }

            // objects
            foreach (ilBookingObject::getList($this->getId()) as $item) {
                $bobj = new ilBookingObject($item["booking_object_id"]);
                $bobj->doClone($new_obj->getId(), $smap);
            }

            $new_obj->update();

            return $new_obj;
        }
        return null;
    }

    public function setOffline(
        bool $a_value = true
    ): void {
        $this->offline = $a_value;
    }

    public function isOffline(): bool
    {
        return $this->offline;
    }

    /**
     * Toggle public log property
     */
    public function setPublicLog(
        bool $a_value = true
    ): void {
        $this->public_log = $a_value;
    }

    public function hasPublicLog(): bool
    {
        return $this->public_log;
    }

    public function setScheduleType(int $a_value): void
    {
        $this->schedule_type = $a_value;
    }

    public function getScheduleType(): int
    {
        return $this->schedule_type;
    }

    public function setReminderStatus(int $a_val): void
    {
        $this->reminder_status = $a_val;
    }

    public function getReminderStatus(): int
    {
        return $this->reminder_status;
    }

    public function setReminderDay(int $a_val): void
    {
        $this->reminder_day = $a_val;
    }

    public function getReminderDay(): int
    {
        return $this->reminder_day;
    }

    public function setPreferenceNumber(int $a_val): void
    {
        $this->preference_nr = $a_val;
    }

    public function getPreferenceNumber(): int
    {
        return $this->preference_nr;
    }

    /**
     * @param int $a_val preference deadline unix timestamp
     */
    public function setPreferenceDeadline(int $a_val): void
    {
        $this->pref_deadline = $a_val;
    }

    /**
     * @return int preference deadline unix timestamp
     */
    public function getPreferenceDeadline(): int
    {
        return $this->pref_deadline;
    }

    public static function _lookupOnline(int $a_obj_id): bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query("SELECT pool_offline" .
            " FROM booking_settings" .
            " WHERE booking_pool_id = " . $ilDB->quote($a_obj_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        return !$row["pool_offline"];
    }

    /**
     * Set overall / global booking limit
     */
    public function setOverallLimit(?int $a_value = null): void
    {
        $this->overall_limit = $a_value;
    }

    public function getOverallLimit(): ?int
    {
        return $this->overall_limit;
    }

    /**
     * Set reservation filter period default
     */
    public function setReservationFilterPeriod(
        ?int $a_value = null
    ): void {
        $this->reservation_period = $a_value;
    }

    public function getReservationFilterPeriod(): ?int
    {
        return $this->reservation_period;
    }


    //
    // advanced metadata
    //

    public static function getAdvancedMDFields(
        int $a_ref_id
    ): array {
        $fields = array();

        $recs = ilAdvancedMDRecord::_getSelectedRecordsByObject("book", $a_ref_id, "bobj");

        foreach ($recs as $record_obj) {
            foreach (ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_obj->getRecordId()) as $def) {
                $field_id = $def->getFieldId();
                $fields[$field_id] = array(
                    "id" => $field_id,
                    "title" => $def->getTitle(),
                    "type" => $def->getType()
                );
            }
        }

        return $fields;
    }
}
