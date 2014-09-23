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
	var $db = null;

	var $obj_id = null;
	var $obj_type = null;
	var $obj_mode = null;
	var $visits = null;

	var $is_stored = false;
		 	
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
	// const LP_MODE_SURVEY_FINISHED = 18; (placeholder for 4.6.x)

	const LP_DEFAULT_VISITS = 30;

	function ilLPObjSettings($a_obj_id)
	{
		global $ilObjDataCache, $ilDB;

		$this->db = $ilDB;
		$this->obj_id = $a_obj_id;

		if(!$this->__read())
		{
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
		global $ilDB;

	 	$query = "INSERT INTO ut_lp_settings (obj_id,obj_type,u_mode,visits) ".
	 		"VALUES( ".
	 		$this->db->quote($a_new_obj_id ,'integer').", ".
	 		$this->db->quote($this->getObjType() ,'text').", ".
	 		$this->db->quote($this->getMode() ,'integer').", ".
	 		$this->db->quote($this->getVisits() ,'integer').
	 		")";
	 	$res = $ilDB->manipulate($query);
		return true;
	}

	function getVisits()
	{
		return (int) $this->visits ? $this->visits : self::LP_DEFAULT_VISITS;
	}

	function setVisits($a_visits)
	{
		$this->visits = $a_visits;
	}

	function setMode($a_mode)
	{		
		$this->obj_mode = $a_mode;
	}
	
	function getMode()
	{
		return $this->obj_mode;
	}

	function getObjId()
	{
		return (int) $this->obj_id;
	}
	
	function getObjType()
	{
		return $this->obj_type;
	}
	
	function __read()
	{
		$res = $this->db->query("SELECT * FROM ut_lp_settings WHERE obj_id = ".
			$this->db->quote($this->obj_id ,'integer'));
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->is_stored = true;
			$this->obj_type = $row->obj_type;
			$this->obj_mode = $row->u_mode;
			$this->visits = $row->visits;

			return true;
		}

		return false;
	}

	function update($a_refresh_lp = true)
	{
		global $ilDB;
		
		if(!$this->is_stored)
		{
			return $this->insert();
		}
		$query = "UPDATE ut_lp_settings SET u_mode = ".$ilDB->quote($this->getMode() ,'integer').", ".
			"visits = ".$ilDB->quote($this->getVisits() ,'integer')." ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId() ,'integer');
		$res = $ilDB->manipulate($query);
		$this->__read();
		
		if($a_refresh_lp)
		{
			$this->doLPRefresh();		
		}
		
		return true;
	}
	
	function insert()
	{
		global $ilDB,$ilLog;
		
		$ilLog->logStack();
		
		$query = "INSERT INTO ut_lp_settings (obj_id,obj_type,u_mode,visits) ".
			"VALUES(".
			$ilDB->quote($this->getObjId() ,'integer').", ".
			$ilDB->quote($this->getObjType(),'text').", ".
			$ilDB->quote($this->getMode(),'integer').", ".
			$ilDB->quote($this->getVisits(), 'integer').  // #12482
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

	function _delete($a_obj_id)
	{
		global $ilDB;

		$query = "DELETE FROM ut_lp_settings WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer');
		$res = $ilDB->manipulate($query);

		return true;
	}


	// Static
	
	function _lookupVisits($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT visits FROM ut_lp_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->visits;
		}
		return self::LP_DEFAULT_VISITS;
	}
	
	public static function _lookupDBModeForObjects(array $a_obj_ids)
	{
		global $ilDB;
		
		// this does NOT handle default mode!
		
		$res = array();
		
		$query = "SELECT obj_id, u_mode FROM ut_lp_settings".
			" WHERE ".$ilDB->in("obj_id", $a_obj_ids, "", "integer");
		$set = $ilDB->query($query);
		while($row = $set->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$res[$row->obj_id] = $row->u_mode;
		}
		
		return $res;
	}

	public static function _lookupDBMode($a_obj_id)
	{
		global $ilDB;
		
		// this does NOT handle default mode!

		$query = "SELECT u_mode FROM ut_lp_settings".
			" WHERE obj_id = ".$ilDB->quote($a_obj_id, "integer");
		$set = $ilDB->query($query);
		$row = $ilDB->fetchAssoc($set);
		if(is_array($row))
		{
			return $row['u_mode'];		
		}
	}
		
	public static function _mode2Text($a_mode)
	{	
		global $lng;

		switch($a_mode)
		{
			case self::LP_MODE_DEACTIVATED:
				return $lng->txt('trac_mode_deactivated');

			case self::LP_MODE_TLT:
				return $lng->txt('trac_mode_tlt');

			case self::LP_MODE_VISITS:
				return $lng->txt('trac_mode_visits');
				
			case self::LP_MODE_MANUAL:
				return $lng->txt('trac_mode_manual');

			case self::LP_MODE_MANUAL_BY_TUTOR:
				return $lng->txt('trac_mode_manual_by_tutor');

			case self::LP_MODE_OBJECTIVES:
				return $lng->txt('trac_mode_objectives');

			case self::LP_MODE_COLLECTION:
				return $lng->txt('trac_mode_collection');

			case self::LP_MODE_SCORM:
				return $lng->txt('trac_mode_scorm');

			case self::LP_MODE_TEST_FINISHED:
				return $lng->txt('trac_mode_test_finished');

			case self::LP_MODE_TEST_PASSED:
				return $lng->txt('trac_mode_test_passed');

			case self::LP_MODE_EXERCISE_RETURNED:
				return $lng->txt('trac_mode_exercise_returned');
			
			case self::LP_MODE_SCORM_PACKAGE:
				return $lng->txt('trac_mode_scorm_package');
				
			case self::LP_MODE_EVENT:
				return $lng->txt('trac_mode_event');
				
			case self::LP_MODE_PLUGIN:
				return $lng->txt('trac_mode_plugin');
				
			case self::LP_MODE_COLLECTION_MANUAL:
				return $lng->txt('trac_mode_collection_manual');
				
			case self::LP_MODE_COLLECTION_TLT:
				return $lng->txt('trac_mode_collection_tlt');
				
			case self::LP_MODE_QUESTIONS:
				return $lng->txt('trac_mode_questions');
		}
	}
	
	public static function _mode2InfoText($a_mode)
	{
		global $lng;
		
		switch($a_mode)
		{
			case self::LP_MODE_DEACTIVATED:
				return $lng->txt('trac_mode_deactivated_info_new');

			case self::LP_MODE_TLT:
				include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
				return sprintf($lng->txt('trac_mode_tlt_info'), ilObjUserTracking::_getValidTimeSpan());

			case self::LP_MODE_VISITS:
				return $lng->txt('trac_mode_visits_info');
				
			case self::LP_MODE_MANUAL:
				return $lng->txt('trac_mode_manual_info');
				
			case self::LP_MODE_MANUAL_BY_TUTOR:
				return $lng->txt('trac_mode_manual_by_tutor_info');

			case self::LP_MODE_OBJECTIVES:
				return $lng->txt('trac_mode_objectives_info');

			case self::LP_MODE_COLLECTION:
				return $lng->txt('trac_mode_collection_info');

			case self::LP_MODE_SCORM:
				return $lng->txt('trac_mode_scorm_info');

			case self::LP_MODE_TEST_FINISHED:
				return $lng->txt('trac_mode_test_finished_info');

			case self::LP_MODE_TEST_PASSED:
				return $lng->txt('trac_mode_test_passed_info');

			case self::LP_MODE_EXERCISE_RETURNED:
				return $lng->txt('trac_mode_exercise_returned_info');

			case self::LP_MODE_SCORM_PACKAGE:
				return $lng->txt('trac_mode_scorm_package_info');
				
			case self::LP_MODE_EVENT:
				return $lng->txt('trac_mode_event_info');
				
			case self::LP_MODE_COLLECTION_MANUAL:
				return $lng->txt('trac_mode_collection_manual_info');
				
			case self::LP_MODE_COLLECTION_TLT:
				return $lng->txt('trac_mode_collection_tlt_info');
				
			case self::LP_MODE_QUESTIONS:
				return $lng->txt('trac_mode_questions_info');
		}
	}
}

?>