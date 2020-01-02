<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilLPObjSettings
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @package ilias-tracking
*
*/
class ilLPObjSettings
{
    public $db = null;

    public $obj_id = null;
    public $obj_type = null;
    public $obj_mode = null;
    public $visits = null;

    public $is_stored = false;
            
    const LP_MODE_DEACTIVATED = 0;
    const LP_MODE_TLT = 1;
    const LP_MODE_VISITS = 2;
    const LP_MODE_MANUAL = 3;
    const LP_MODE_OBJECTIVES = 4;
    const LP_MODE_COLLECTION = 5;
    const LP_MODE_SCORM = 6;
    const LP_MODE_TEST_FINISHED = 7;
    const LP_MODE_TEST_PASSED = 8;
    const LP_MODE_EXERCISE_RETURNED = 9;
    const LP_MODE_EVENT = 10;
    const LP_MODE_MANUAL_BY_TUTOR = 11;
    const LP_MODE_SCORM_PACKAGE = 12;
    const LP_MODE_UNDEFINED = 13;
    const LP_MODE_PLUGIN = 14;
    const LP_MODE_COLLECTION_TLT = 15;
    const LP_MODE_COLLECTION_MANUAL = 16;
    const LP_MODE_QUESTIONS = 17;
    const LP_MODE_SURVEY_FINISHED = 18;
    const LP_MODE_VISITED_PAGES = 19;
    const LP_MODE_CONTENT_VISITED = 20;
    const LP_MODE_COLLECTION_MOBS = 21;
    const LP_MODE_STUDY_PROGRAMME = 22;
    const LP_MODE_INDIVIDUAL_ASSESSMENT = 23;

    const LP_DEFAULT_VISITS = 30; // ???
    
    protected static $map = array(
        
        self::LP_MODE_DEACTIVATED => array('ilLPStatus',
            'trac_mode_deactivated', 'trac_mode_deactivated_info_new')
        
        ,self::LP_MODE_TLT => array('ilLPStatusTypicalLearningTime',
            'trac_mode_tlt', 'trac_mode_tlt_info') // info has dynamic part!
        
        ,self::LP_MODE_VISITS => array('ilLPStatusVisits',
            'trac_mode_visits', 'trac_mode_visits_info')
        
        ,self::LP_MODE_MANUAL => array('ilLPStatusManual',
            'trac_mode_manual', 'trac_mode_manual_info')
        
        ,self::LP_MODE_OBJECTIVES => array('ilLPStatusObjectives',
            'trac_mode_objectives', 'trac_mode_objectives_info')
        
        ,self::LP_MODE_COLLECTION => array('ilLPStatusCollection',
            'trac_mode_collection', 'trac_mode_collection_info')
        
        ,self::LP_MODE_SCORM => array('ilLPStatusSCORM',
            'trac_mode_scorm', 'trac_mode_scorm_info')
        
        ,self::LP_MODE_TEST_FINISHED => array('ilLPStatusTestFinished',
            'trac_mode_test_finished', 'trac_mode_test_finished_info')
        
        ,self::LP_MODE_TEST_PASSED => array('ilLPStatusTestPassed',
            'trac_mode_test_passed', 'trac_mode_test_passed_info')
        
        ,self::LP_MODE_EXERCISE_RETURNED => array('ilLPStatusExerciseReturned',
            'trac_mode_exercise_returned', 'trac_mode_exercise_returned_info')
        
        ,self::LP_MODE_EVENT => array('ilLPStatusEvent',
            'trac_mode_event', 'trac_mode_event_info')
        
        ,self::LP_MODE_MANUAL_BY_TUTOR => array('ilLPStatusManualByTutor',
            'trac_mode_manual_by_tutor', 'trac_mode_manual_by_tutor_info')
        
        ,self::LP_MODE_SCORM_PACKAGE => array('ilLPStatusSCORMPackage',
            'trac_mode_scorm_package', 'trac_mode_scorm_package_info')
        
        ,self::LP_MODE_UNDEFINED => null
            
        ,self::LP_MODE_PLUGIN => array('ilLPStatusPlugin',
            'trac_mode_plugin', '') // no settings screen, so no info needed
        
        ,self::LP_MODE_COLLECTION_TLT => array('ilLPStatusCollectionTLT',
            'trac_mode_collection_tlt', 'trac_mode_collection_tlt_info')
        
        ,self::LP_MODE_COLLECTION_MANUAL => array('ilLPStatusCollectionManual',
            'trac_mode_collection_manual', 'trac_mode_collection_manual_info')
        
        ,self::LP_MODE_QUESTIONS => array('ilLPStatusQuestions',
            'trac_mode_questions', 'trac_mode_questions_info')
        
        ,self::LP_MODE_SURVEY_FINISHED => array('ilLPStatusSurveyFinished',
            'trac_mode_survey_finished', 'trac_mode_survey_finished_info')
        
        ,self::LP_MODE_VISITED_PAGES => array('ilLPStatusVisitedPages',
            'trac_mode_visited_pages', 'trac_mode_visited_pages_info')
        
        ,self::LP_MODE_CONTENT_VISITED => array('ilLPStatusContentVisited',
            'trac_mode_content_visited', 'trac_mode_content_visited_info')
        
        ,self::LP_MODE_COLLECTION_MOBS => array('ilLPStatusCollectionMobs',
            'trac_mode_collection_mobs', 'trac_mode_collection_mobs_info')
        
        ,self::LP_MODE_STUDY_PROGRAMME => array('ilLPStatusStudyProgramme',
            'trac_mode_study_programme', '')

        ,self::LP_MODE_INDIVIDUAL_ASSESSMENT => array('ilLPStatusIndividualAssessment',
            'trac_mode_individual_assessment', 'trac_mode_individual_assessment_info')
    );

    public function __construct($a_obj_id)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];
        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;
        $this->obj_id = $a_obj_id;

        if (!$this->__read()) {
            $this->obj_type = $ilObjDataCache->lookupType($this->obj_id);
            
            include_once "Services/Object/classes/class.ilObjectLP.php";
            $olp = ilObjectLP::getInstance($this->obj_id);
            $this->obj_mode = $olp->getDefaultMode();
        }
    }
    
    /**
     * Clone settings
     *
     * @access public
     * @param int new obj id
     *
     */
    public function cloneSettings($a_new_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "INSERT INTO ut_lp_settings (obj_id,obj_type,u_mode,visits) " .
            "VALUES( " .
            $this->db->quote($a_new_obj_id, 'integer') . ", " .
            $this->db->quote($this->getObjType(), 'text') . ", " .
            $this->db->quote($this->getMode(), 'integer') . ", " .
            $this->db->quote($this->getVisits(), 'integer') .
            ")";
        $res = $ilDB->manipulate($query);
        return true;
    }

    public function getVisits()
    {
        return (int) $this->visits ? $this->visits : self::LP_DEFAULT_VISITS;
    }

    public function setVisits($a_visits)
    {
        $this->visits = $a_visits;
    }

    public function setMode($a_mode)
    {
        $this->obj_mode = $a_mode;
    }
    
    public function getMode()
    {
        return $this->obj_mode;
    }

    public function getObjId()
    {
        return (int) $this->obj_id;
    }
    
    public function getObjType()
    {
        return $this->obj_type;
    }
    
    public function __read()
    {
        $res = $this->db->query("SELECT * FROM ut_lp_settings WHERE obj_id = " .
            $this->db->quote($this->obj_id, 'integer'));
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->is_stored = true;
            $this->obj_type = $row->obj_type;
            $this->obj_mode = $row->u_mode;
            $this->visits = $row->visits;

            return true;
        }

        return false;
    }

    public function update($a_refresh_lp = true)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->is_stored) {
            return $this->insert();
        }
        $query = "UPDATE ut_lp_settings SET u_mode = " . $ilDB->quote($this->getMode(), 'integer') . ", " .
            "visits = " . $ilDB->quote($this->getVisits(), 'integer') . " " .
            "WHERE obj_id = " . $ilDB->quote($this->getObjId(), 'integer');
        $res = $ilDB->manipulate($query);
        $this->__read();
        
        if ($a_refresh_lp) {
            $this->doLPRefresh();
        }
        
        return true;
    }
    
    public function insert()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "INSERT INTO ut_lp_settings (obj_id,obj_type,u_mode,visits) " .
            "VALUES(" .
            $ilDB->quote($this->getObjId(), 'integer') . ", " .
            $ilDB->quote($this->getObjType(), 'text') . ", " .
            $ilDB->quote($this->getMode(), 'integer') . ", " .
            $ilDB->quote($this->getVisits(), 'integer') .  // #12482
            ")";
        $res = $ilDB->manipulate($query);
        $this->__read();
    
        $this->doLPRefresh();

        return true;
    }

    protected function doLPRefresh()
    {
        // refresh learning progress
        include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
        ilLPStatusWrapper::_refreshStatus($this->getObjId());
    }

    public static function _delete($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "DELETE FROM ut_lp_settings WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->manipulate($query);

        return true;
    }


    // Static
    
    public static function _lookupVisits($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT visits FROM ut_lp_settings " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->visits;
        }
        return self::LP_DEFAULT_VISITS;
    }
    
    public static function _lookupDBModeForObjects(array $a_obj_ids)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // this does NOT handle default mode!
        
        $res = array();
        
        $query = "SELECT obj_id, u_mode FROM ut_lp_settings" .
            " WHERE " . $ilDB->in("obj_id", $a_obj_ids, "", "integer");
        $set = $ilDB->query($query);
        while ($row = $set->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $res[$row->obj_id] = $row->u_mode;
        }
        
        return $res;
    }

    public static function _lookupDBMode($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // this does NOT handle default mode!

        $query = "SELECT u_mode FROM ut_lp_settings" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer");
        $set = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($set);
        if (is_array($row)) {
            return $row['u_mode'];
        }
    }
        
    public static function _mode2Text($a_mode)
    {
        global $DIC;

        $lng = $DIC['lng'];

        if (array_key_exists($a_mode, self::$map) &&
            is_array(self::$map[$a_mode])) {
            return $lng->txt(self::$map[$a_mode][1]);
        }
    }
    
    public static function _mode2InfoText($a_mode)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        if (array_key_exists($a_mode, self::$map) &&
            is_array(self::$map[$a_mode])) {
            $info = $lng->txt(self::$map[$a_mode][2]);
                        
            if ($a_mode == self::LP_MODE_TLT) {
                // dynamic content
                include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
                $info = sprintf($info, ilObjUserTracking::_getValidTimeSpan());
            }
            
            return $info;
        }
    }
    
    public static function getClassMap()
    {
        $res = array();
        foreach (self::$map as $mode => $item) {
            $res[$mode] = $item[0];
        }
        return $res;
    }
    
    public static function _deleteByObjId($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // we are only removing settings for now
        // invalid ut_lp_collections-entries are filtered
        // ut_lp_marks is deemed private user data
        
        $ilDB->manipulate("DELETE FROM ut_lp_settings" .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, "integer"));
    }
}
