<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjBookingPool
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
*/
class ilObjBookingPool extends ilObject
{
    //offline default should be true
    protected $offline = true;			// [bool]
    protected $public_log;		// [bool]
    protected $schedule_type;	// [int]
    protected $overall_limit;   // [int]
    protected $reservation_period; // [int]
    protected $reminder_status = 0; // [int]
    protected $reminder_day = 1; // [int]

    const TYPE_FIX_SCHEDULE = 1;
    const TYPE_NO_SCHEDULE = 2;
    
    /**
    * Constructor
    * @param	int		$a_id					reference_id or object_id
    * @param	bool	$a_call_by_reference	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->type = "book";
        $this->setScheduleType(self::TYPE_FIX_SCHEDULE);
        parent::__construct($a_id, $a_call_by_reference);
    }
    
    /**
     * Parse properties for sql statements
     */
    protected function getDBFields()
    {
        $fields = array(
            "schedule_type" => array("integer", $this->getScheduleType()),
            "pool_offline" => array("integer", $this->isOffline()),
            "public_log" => array("integer", $this->hasPublicLog()),
            "ovlimit" => array("integer", $this->getOverallLimit()),
            "reminder_status" => array("integer", $this->getReminderStatus()),
            "reminder_day" => array("integer", $this->getReminderDay()),
            "rsv_filter_period" => array("integer", $this->getReservationFilterPeriod())
        );
        
        return $fields;
    }

    /**
    * create object
    * @return	integer
    */
    public function create()
    {
        $ilDB = $this->db;
        
        $new_id = parent::create();
        
        $fields = $this->getDBFields();
        $fields["booking_pool_id"] = array("integer", $new_id);

        $ilDB->insert("booking_settings", $fields);

        return $new_id;
    }

    /**
    * update object data
    * @return	boolean
    */
    public function update()
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

    public function read()
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
        }
    }

    /**
     * Get poos with reminders
     *
     * @return array[]
     */
    public static function getPoolsWithReminders()
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
     * Write last reminder timestamp
     *
     * @param int pool id
     * @param int timestamp
     */
    public static function writeLastReminderTimestamp($a_obj_id, $a_ts)
    {
        global $DIC;
        $db = $DIC->database();
        $db->update("booking_settings", array(
                "last_remind_ts" => array("integer", $a_ts)
            ), array(	// where
                "booking_pool_id" => array("integer", $a_obj_id)
            ));
    }


    /**
    * delete object and all related data
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
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

        if (sizeof($objects)) {
            $ilDB->manipulate('DELETE FROM booking_reservation' .
                    ' WHERE ' . $ilDB->in('object_id', $objects, '', 'integer'));
        }

        $ilDB->manipulate('DELETE FROM booking_object' .
            ' WHERE pool_id = ' . $ilDB->quote($id, 'integer'));

        return true;
    }
    
    public function cloneObject($a_target_id, $a_copy_id = 0, $a_omit_tree = false)
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);

        //copy online status if object is not the root copy object
        $cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

        if (!$cp_options->isRootNode($this->getRefId())) {
            $new_obj->setOffline($this->isOffline());
        }

        $new_obj->setScheduleType($this->getScheduleType());
        $new_obj->setPublicLog($this->hasPublicLog());
        $new_obj->setOverallLimit($this->getOverallLimit());
        $new_obj->setReminderStatus($this->getReminderStatus());
        $new_obj->setReminderDay($this->getReminderDay());

        $smap = null;
        if ($this->getScheduleType() == self::TYPE_FIX_SCHEDULE) {
            // schedules
            include_once "Modules/BookingManager/classes/class.ilBookingSchedule.php";
            foreach (ilBookingSchedule::getList($this->getId()) as $item) {
                $schedule = new ilBookingSchedule($item["booking_schedule_id"]);
                $smap[$item["booking_schedule_id"]] = $schedule->doClone($new_obj->getId());
            }
        }
        
        // objects
        include_once "Modules/BookingManager/classes/class.ilBookingObject.php";
        foreach (ilBookingObject::getList($this->getId()) as $item) {
            $bobj = new ilBookingObject($item["booking_object_id"]);
            $bobj->doClone($new_obj->getId(), $smap);
        }
        
        $new_obj->update();
        
        return $new_obj;
    }
    
    /**
     * Toggle offline property
     * @param bool $a_value
     */
    public function setOffline($a_value = true)
    {
        $this->offline = (bool) $a_value;
    }

    /**
     * Get offline property
     * @return bool
     */
    public function isOffline()
    {
        return (bool) $this->offline;
    }

    /**
     * Toggle public log property
     * @param bool $a_value
     */
    public function setPublicLog($a_value = true)
    {
        $this->public_log = (bool) $a_value;
    }

    /**
     * Get public log property
     * @return bool
     */
    public function hasPublicLog()
    {
        return (bool) $this->public_log;
    }

    /**
     * Set schedule type
     * @param int $a_value
     */
    public function setScheduleType($a_value)
    {
        $this->schedule_type = (int) $a_value;
    }

    /**
     * Get schedule type
     * @return int
     */
    public function getScheduleType()
    {
        return $this->schedule_type;
    }
    
    /**
     * Set reminder status
     *
     * @param int $a_val reminder status
     */
    public function setReminderStatus($a_val)
    {
        $this->reminder_status = $a_val;
    }
    
    /**
     * Get reminder status
     *
     * @return int reminder status
     */
    public function getReminderStatus()
    {
        return $this->reminder_status;
    }

    /**
     * Set reminder day
     *
     * @param int $a_val reminder day
     */
    public function setReminderDay($a_val)
    {
        $this->reminder_day = $a_val;
    }

    /**
     * Get reminder day
     *
     * @return int reminder day
     */
    public function getReminderDay()
    {
        return $this->reminder_day;
    }
    
    /**
     * Check object status
     *
     * @param int $a_obj_id
     * @return boolean
     */
    public static function _lookupOnline($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC->database();
        
        $set = $ilDB->query("SELECT pool_offline" .
            " FROM booking_settings" .
            " WHERE booking_pool_id = " . $ilDB->quote($a_obj_id, "integer"));
        $row = $ilDB->fetchAssoc($set);
        return !(bool) $row["pool_offline"];
    }
    
    /**
     * Set overall / global booking limit
     *
     * @param int $a_value
     */
    public function setOverallLimit($a_value = null)
    {
        if ($a_value !== null) {
            $a_value = (int) $a_value;
        }
        $this->overall_limit = $a_value;
    }
    
    /**
     * Get overall / global booking limit
     *
     * @return int $a_value
     */
    public function getOverallLimit()
    {
        return $this->overall_limit;
    }
    
    /**
     * Set reservation filter period default
     *
     * @param int $a_value
     */
    public function setReservationFilterPeriod($a_value = null)
    {
        if ($a_value !== null) {
            $a_value = (int) $a_value;
        }
        $this->reservation_period = $a_value;
    }
    
    /**
     * Get reservation filter period default
     *
     * @return int
     */
    public function getReservationFilterPeriod()
    {
        return $this->reservation_period;
    }
    
    
    //
    // advanced metadata
    //
    
    public static function getAdvancedMDFields($a_ref_id)
    {
        $fields = array();
        
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php');
        $recs = ilAdvancedMDRecord::_getSelectedRecordsByObject("book", $a_ref_id, "bobj");

        foreach ($recs as $record_obj) {
            include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
            foreach (ilAdvancedMDFieldDefinition::getInstancesByRecordId($record_obj->getRecordId()) as $def) {
                $fields[$def->getFieldId()] = array(
                    "id" => $def->getFieldId(),
                    "title" => $def->getTitle(),
                    "type" => $def->getType()
                );
            }
        }

        return $fields;
    }
}
