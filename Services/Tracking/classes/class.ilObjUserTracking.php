<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjUserTracking
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Jens Conze <jc@databay.de>
*
* @version $Id$
*
* @extends ilObject
* @package ilias-core
*/

include_once "./Services/Object/classes/class.ilObject.php";

class ilObjUserTracking extends ilObject
{
    public $valid_time;
    public $extended_data;
    public $learning_progress;
    public $tracking_user_related;
    public $object_statistics_enabled;
    public $lp_learner;
    public $session_statistics_enabled;
    public $lp_list_gui;

    // BEGIN ChangeEvent
    /**
     * This variable holds the enabled state of the change event tracking.
     */
    private $is_change_event_tracking_enabled;
    // BEGIN ChangeEvent

    const EXTENDED_DATA_LAST_ACCESS = 1;
    const EXTENDED_DATA_READ_COUNT = 2;
    const EXTENDED_DATA_SPENT_SECONDS = 4;
    
    const DEFAULT_TIME_SPAN = 300;

    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "trac";
        parent::__construct($a_id, $a_call_by_reference);

        $this->__readSettings();
    }

    public function enableLearningProgress($a_enable)
    {
        $this->learning_progress = (bool) $a_enable;
    }

    public function enabledLearningProgress()
    {
        return $this->learning_progress;
    }

    /**
    * check wether learing progress is enabled or not
    */
    public static function _enabledLearningProgress()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        return (bool) $ilSetting->get("enable_tracking", 0);
    }

    /**
    * enable tracking of user related data
    */
    public function enableUserRelatedData($a_enable)
    {
        $this->tracking_user_related = (bool) $a_enable;
    }

    public function enabledUserRelatedData()
    {
        return (bool) $this->tracking_user_related;
    }

    /**
    * check wether user related tracking is enabled or not
    */
    public static function _enabledUserRelatedData()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        return (bool) $ilSetting->get('save_user_related_data');
    }
    
    /**
    * check wether object statistics is enabled or not
    */
    public static function _enabledObjectStatistics()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        return (bool) $ilSetting->get('object_statistics', 0);
    }
    
    /**
    * Sets the object statistics property.
    *
    * @param	boolean	new value
    * @return	void
    */
    public function enableObjectStatistics($newValue)
    {
        $this->object_statistics_enabled = (bool) $newValue;
    }
    /**
    * Gets the object statistic property.
    *
    * @return	boolean	value
    */
    public function enabledObjectStatistics()
    {
        return (bool) $this->object_statistics_enabled;
    }
    
    /**
    * Sets the session statistics property.
    *
    * @param	boolean	new value
    * @return	void
    */
    public function enableSessionStatistics($newValue)
    {
        $this->session_statistics_enabled = (bool) $newValue;
    }
    /**
    * Gets the session statistic property.
    *
    * @return	boolean	value
    */
    public function enabledSessionStatistics()
    {
        return (bool) $this->session_statistics_enabled;
    }
    
    /**
    * check wether session statistics is enabled or not
    */
    public static function _enabledSessionStatistics()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        return (bool) $ilSetting->get('session_statistics', 1);
    }

    public function setValidTimeSpan($a_time_span)
    {
        $this->valid_time = (int) $a_time_span;
    }

    public function getValidTimeSpan()
    {
        return (int) $this->valid_time;
    }
    
    public static function _getValidTimeSpan()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        return (int) $ilSetting->get("tracking_time_span", self::DEFAULT_TIME_SPAN);
    }

    // BEGIN ChangeEvent
    /**
    * Sets the changeEventTrackingEnabled property.
    *
    * @param	boolean	new value
    * @return	void
    */
    public function enableChangeEventTracking($newValue)
    {
        $this->is_change_event_tracking_enabled = (bool) $newValue;
    }
    /**
    * Gets the changeEventTrackingEnabled property.
    *
    * @return	boolean	value
    */
    public function enabledChangeEventTracking()
    {
        return (bool) $this->is_change_event_tracking_enabled;
    }
    // END ChangeEvent
    
    public function setExtendedData($a_value)
    {
        $this->extended_data = $a_value;
    }

    public function hasExtendedData($a_code)
    {
        return (int) $this->extended_data & (int) $a_code;
    }

    public function updateSettings()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $ilSetting->set("enable_tracking", (int) $this->enabledLearningProgress());
        $ilSetting->set("save_user_related_data", (int) $this->enabledUserRelatedData());
        $ilSetting->set("tracking_time_span", $this->getValidTimeSpan());
        $ilSetting->set("lp_extended_data", $this->extended_data);
        $ilSetting->set("object_statistics", (int) $this->enabledObjectStatistics());
        // $ilSetting->set("lp_desktop", (int)$this->hasLearningProgressDesktop());
        $ilSetting->set("lp_learner", (int) $this->hasLearningProgressLearner());
        $ilSetting->set("session_statistics", (int) $this->enabledSessionStatistics());
        $ilSetting->set("lp_list_gui", (int) $this->hasLearningProgressListGUI());

        /* => REPOSITORY
        // BEGIN ChangeEvent
        require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
        if ($this->enabledChangeEventTracking() != ilChangeEvent::_isActive())
        {
            if ($this->enabledChangeEventTracking())
            {
                ilChangeEvent::_activate();
            }
            else
            {
                ilChangeEvent::_deactivate();
            }
        }
        // END ChangeEvent
        */
        
        return true;
    }

    protected function __readSettings()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $this->enableLearningProgress($ilSetting->get("enable_tracking", 0));
        $this->enableUserRelatedData($ilSetting->get("save_user_related_data", 0));
        $this->enableObjectStatistics($ilSetting->get("object_statistics", 0));
        $this->setValidTimeSpan($ilSetting->get("tracking_time_span", self::DEFAULT_TIME_SPAN));
        // $this->setLearningProgressDesktop($ilSetting->get("lp_desktop", 1));
        $this->setLearningProgressLearner($ilSetting->get("lp_learner", 1));
        $this->enableSessionStatistics($ilSetting->get("session_statistics", 1));
        $this->setLearningProgressListGUI($ilSetting->get("lp_list_gui", 0));

        // BEGIN ChangeEvent
        require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
        $this->enableChangeEventTracking(ilChangeEvent::_isActive());
        // END ChangeEvent
        
        $this->setExtendedData($ilSetting->get("lp_extended_data"), 0);

        return true;
    }

    public static function _deleteUser($a_usr_id)
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

        $query = "DELETE FROM ut_lp_marks WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);

        $ilDB->manipulate("DELETE FROM ut_online WHERE usr_id = " .
            $ilDB->quote($a_usr_id, "integer"));

        return true;
    }
    
    public static function _hasLearningProgressOtherUsers()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        
        $obj_id = array_pop(array_keys(ilObject::_getObjectsByType("trac")));
        $ref_id = array_pop(ilObject::_getAllReferences($obj_id));
        
        return $rbacsystem->checkAccess("lp_other_users", $ref_id);
    }
    
    public function setLearningProgressLearner($a_value)
    {
        $this->lp_learner = (bool) $a_value;
    }
    
    public function hasLearningProgressLearner()
    {
        return (bool) $this->lp_learner;
    }
    
    public static function _hasLearningProgressLearner()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
                    
        return (bool) $ilSetting->get("lp_learner", 1);
    }
    
    public function setLearningProgressListGUI($a_value)
    {
        $this->lp_list_gui = (bool) $a_value;
    }
    
    public function hasLearningProgressListGUI()
    {
        return (bool) $this->lp_list_gui;
    }
    
    public static function _hasLearningProgressListGUI()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
                    
        return (bool) $ilSetting->get("lp_list_gui", 0);
    }
} // END class.ilObjUserTracking
