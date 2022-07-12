<?php declare(strict_types=0);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjUserTracking
 * @author  Alex Killing <alex.killing@gmx.de>
 * @author  Jens Conze <jc@databay.de>
 * @extends ilObject
 * @package ilias-core
 */
class ilObjUserTracking extends ilObject
{
    private int $valid_time = 0;
    protected int $extended_data = 0;
    protected bool $learning_progress = false;
    protected bool $tracking_user_related = false;
    protected bool $object_statistics_enabled = false;
    protected bool $lp_learner = false;
    protected bool $session_statistics_enabled = false;
    protected bool $lp_list_gui = false;

    /**
     * This variable holds the enabled state of the change event tracking.
     */
    private bool $is_change_event_tracking_enabled = false;

    public const EXTENDED_DATA_LAST_ACCESS = 1;
    public const EXTENDED_DATA_READ_COUNT = 2;
    public const EXTENDED_DATA_SPENT_SECONDS = 4;

    public const DEFAULT_TIME_SPAN = 300;

    protected ilSetting $settings;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        $this->settings = $DIC->settings();

        $this->type = "trac";
        parent::__construct($a_id, $a_call_by_reference);
        $this->__readSettings();
    }

    public function enableLearningProgress(bool $a_enable) : void
    {
        $this->learning_progress = $a_enable;
    }

    public function enabledLearningProgress() : bool
    {
        return $this->learning_progress;
    }

    public static function _enabledLearningProgress() : bool
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        return (bool) $ilSetting->get("enable_tracking", '0');
    }

    public function enableUserRelatedData(bool $a_enable) : void
    {
        $this->tracking_user_related = $a_enable;
    }

    public function enabledUserRelatedData() : bool
    {
        return $this->tracking_user_related;
    }

    public static function _enabledUserRelatedData()
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        return (bool) $ilSetting->get('save_user_related_data', '0');
    }

    public static function _enabledObjectStatistics() : bool
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        return (bool) $ilSetting->get('object_statistics', '0');
    }

    public function enableObjectStatistics(bool $newValue) : void
    {
        $this->object_statistics_enabled = $newValue;
    }

    public function enabledObjectStatistics() : bool
    {
        return $this->object_statistics_enabled;
    }

    public function enableSessionStatistics(bool $newValue) : void
    {
        $this->session_statistics_enabled = $newValue;
    }

    public function enabledSessionStatistics() : bool
    {
        return $this->session_statistics_enabled;
    }

    public static function _enabledSessionStatistics() : bool
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        return (bool) $ilSetting->get('session_statistics', '1');
    }

    public function setValidTimeSpan(int $a_time_span) : void
    {
        $this->valid_time = $a_time_span;
    }

    public function getValidTimeSpan() : int
    {
        return $this->valid_time;
    }

    public static function _getValidTimeSpan() : int
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        return (int) $ilSetting->get(
            "tracking_time_span",
            (string) self::DEFAULT_TIME_SPAN
        );
    }

    public function enableChangeEventTracking(bool $newValue) : void
    {
        $this->is_change_event_tracking_enabled = $newValue;
    }

    public function enabledChangeEventTracking() : bool
    {
        return $this->is_change_event_tracking_enabled;
    }

    // END ChangeEvent

    public function setExtendedData(int $a_value) : void
    {
        $this->extended_data = $a_value;
    }

    public function hasExtendedData(int $a_code) : bool
    {
        return (bool) ($this->extended_data & $a_code);
    }

    public function updateSettings()
    {
        $this->settings->set(
            "enable_tracking",
            (string) $this->enabledLearningProgress()
        );
        $this->settings->set(
            "save_user_related_data",
            (string) $this->enabledUserRelatedData()
        );
        $this->settings->set(
            "tracking_time_span",
            (string) $this->getValidTimeSpan()
        );
        $this->settings->set("lp_extended_data", (string) $this->extended_data);
        $this->settings->set(
            "object_statistics",
            (string) $this->enabledObjectStatistics()
        );
        // $this->settings->set("lp_desktop", (int)$this->hasLearningProgressDesktop());
        $this->settings->set(
            "lp_learner",
            (string) $this->hasLearningProgressLearner()
        );
        $this->settings->set(
            "session_statistics",
            (string) $this->enabledSessionStatistics()
        );
        $this->settings->set(
            "lp_list_gui",
            (string) $this->hasLearningProgressListGUI()
        );
    }

    protected function __readSettings() : void
    {
        $this->enableLearningProgress(
            (bool) $this->settings->get("enable_tracking", '0')
        );
        $this->enableUserRelatedData(
            (bool) $this->settings->get("save_user_related_data", '0')
        );
        $this->enableObjectStatistics(
            (bool) $this->settings->get("object_statistics", '0')
        );
        $this->setValidTimeSpan(
            (int) $this->settings->get(
                "tracking_time_span",
                (string) self::DEFAULT_TIME_SPAN
            )
        );
        $this->setLearningProgressLearner(
            (bool) $this->settings->get("lp_learner", '1')
        );
        $this->enableSessionStatistics(
            (bool) $this->settings->get("session_statistics", '1')
        );
        $this->setLearningProgressListGUI(
            (bool) $this->settings->get("lp_list_gui", '0')
        );

        // BEGIN ChangeEvent
        $this->enableChangeEventTracking(ilChangeEvent::_isActive());
        // END ChangeEvent

        $this->setExtendedData(
            (int) $this->settings->get("lp_extended_data", '0')
        );
    }

    public static function _deleteUser(int $a_usr_id) : void
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = sprintf(
            'DELETE FROM read_event WHERE usr_id = %s ',
            $ilDB->quote($a_usr_id, 'integer')
        );
        $aff = $ilDB->manipulate($query);

        $query = sprintf(
            'DELETE FROM write_event WHERE usr_id = %s ',
            $ilDB->quote($a_usr_id, 'integer')
        );
        $aff = $ilDB->manipulate($query);

        $query = "DELETE FROM ut_lp_marks WHERE usr_id = " . $ilDB->quote(
            $a_usr_id,
            'integer'
        ) . " ";
        $res = $ilDB->manipulate($query);

        $ilDB->manipulate(
            "DELETE FROM ut_online WHERE usr_id = " .
            $ilDB->quote($a_usr_id, "integer")
        );
    }

    public static function _hasLearningProgressOtherUsers() : bool
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $obj_ids = array_keys(ilObject::_getObjectsByType("trac"));
        $obj_id = array_pop($obj_ids);
        $ref_ids = ilObject::_getAllReferences($obj_id);
        $ref_id = array_pop($ref_ids);
        return $rbacsystem->checkAccess("lp_other_users", $ref_id);
    }

    public function setLearningProgressLearner(bool $a_value) : void
    {
        $this->lp_learner = $a_value;
    }

    public function hasLearningProgressLearner() : bool
    {
        return $this->lp_learner;
    }

    public static function _hasLearningProgressLearner() : bool
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        return (bool) $ilSetting->get("lp_learner", '1');
    }

    public function setLearningProgressListGUI(bool $a_value) : void
    {
        $this->lp_list_gui = $a_value;
    }

    public function hasLearningProgressListGUI() : bool
    {
        return $this->lp_list_gui;
    }

    public static function _hasLearningProgressListGUI() : bool
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        return (bool) $ilSetting->get("lp_list_gui", '0');
    }
} // END class.ilObjUserTracking
