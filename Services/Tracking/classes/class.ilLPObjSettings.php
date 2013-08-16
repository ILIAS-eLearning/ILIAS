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

define("LP_MODE_DEACTIVATED",0);
define("LP_MODE_TLT",1);
define("LP_MODE_VISITS",2);
define("LP_MODE_MANUAL",3);
define("LP_MODE_OBJECTIVES",4);
define("LP_MODE_COLLECTION",5);
define("LP_MODE_SCORM",6);
define("LP_MODE_TEST_FINISHED",7);
define("LP_MODE_TEST_PASSED",8);
define("LP_MODE_EXERCISE_RETURNED",9);
define("LP_MODE_EVENT",10);
define("LP_MODE_MANUAL_BY_TUTOR",11);
define("LP_MODE_SCORM_PACKAGE",12);
define("LP_MODE_UNDEFINED",13);
define("LP_MODE_PLUGIN",14);
define("LP_MODE_COLLECTION_TLT", 15);
define("LP_MODE_COLLECTION_MANUAL", 16);

define("LP_DEFAULT_VISITS",30);


class ilLPObjSettings
{
	var $db = null;

	var $obj_id = null;
	var $obj_type = null;
	var $obj_mode = null;
	var $visits = null;

	var $is_stored = false;
	
	static private $mode_by_obj_id = array();

	function ilLPObjSettings($a_obj_id)
	{
		global $ilObjDataCache,$ilDB;

		$this->db =& $ilDB;

		$this->obj_id = $a_obj_id;

		if(!$this->__read())
		{
			$this->obj_type = $ilObjDataCache->lookupType($this->obj_id);
			$this->obj_mode = $this->__getDefaultMode($this->obj_id,$this->obj_type);
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
		return (int) $this->visits ? $this->visits : LP_DEFAULT_VISITS;
	}

	function setVisits($a_visits)
	{
		$this->visits = $a_visits;
	}

	function setMode($a_mode)
	{
		self::$mode_by_obj_id[$this->getObjId()] = $a_mode;
		
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

	function update()
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
		
		$this->doLPRefresh();		
		
		return true;
	}
	
	protected function doLPRefresh()
	{		
		// refresh learning progress		
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");				
		ilLPStatusWrapper::_refreshStatus($this->getObjId());
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
			$ilDB->quote(0, 'integer').
			")";
		$res = $ilDB->manipulate($query);
		$this->__read();
	
		$this->doLPRefresh();		

		return true;
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
		return LP_DEFAULT_VISITS;
	}

	function _lookupMode($a_obj_id)
	{
		global $ilDB,$ilObjDataCache;

		if (isset(self::$mode_by_obj_id[$a_obj_id]))
		{
			return self::$mode_by_obj_id[$a_obj_id];
		}

		$query = "SELECT u_mode FROM ut_lp_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			self::$mode_by_obj_id[$a_obj_id] = $row->u_mode;
			return $row->u_mode;
		}
		
		// no db entry exists => return default mode by type
		$def_mode = ilLPObjSettings::__getDefaultMode($a_obj_id,$ilObjDataCache->lookupType($a_obj_id));
		self::$mode_by_obj_id[$a_obj_id] = $def_mode;

		return $def_mode;
	}
		
	public static function _mode2Text($a_mode)
	{	
		global $lng;

		switch($a_mode)
		{
			case LP_MODE_DEACTIVATED:
				return $lng->txt('trac_mode_deactivated');

			case LP_MODE_TLT:
				return $lng->txt('trac_mode_tlt');

			case LP_MODE_VISITS:
				return $lng->txt('trac_mode_visits');
				
			case LP_MODE_MANUAL:
				return $lng->txt('trac_mode_manual');

			case LP_MODE_MANUAL_BY_TUTOR:
				return $lng->txt('trac_mode_manual_by_tutor');

			case LP_MODE_OBJECTIVES:
				return $lng->txt('trac_mode_objectives');

			case LP_MODE_COLLECTION:
				return $lng->txt('trac_mode_collection');

			case LP_MODE_SCORM:
				return $lng->txt('trac_mode_scorm');

			case LP_MODE_TEST_FINISHED:
				return $lng->txt('trac_mode_test_finished');

			case LP_MODE_TEST_PASSED:
				return $lng->txt('trac_mode_test_passed');

			case LP_MODE_EXERCISE_RETURNED:
				return $lng->txt('trac_mode_exercise_returned');
			
			case LP_MODE_SCORM_PACKAGE:
				return $lng->txt('trac_mode_scorm_package');
				
			case LP_MODE_EVENT:
				return $lng->txt('trac_mode_event');
				
			case LP_MODE_PLUGIN:
				return $lng->txt('trac_mode_plugin');
		}
	}
	
	public static function _mode2InfoText($a_mode)
	{
		global $lng;
		
		switch($a_mode)
		{
			case LP_MODE_DEACTIVATED:
				return $lng->txt('trac_mode_deactivated_info_new');

			case LP_MODE_TLT:
				include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
				return sprintf($lng->txt('trac_mode_tlt_info'), ilObjUserTracking::_getValidTimeSpan());

			case LP_MODE_VISITS:
				return $lng->txt('trac_mode_visits_info');
				
			case LP_MODE_MANUAL:
				return $lng->txt('trac_mode_manual_info');
				
			case LP_MODE_MANUAL_BY_TUTOR:
				return $lng->txt('trac_mode_manual_by_tutor_info');

			case LP_MODE_OBJECTIVES:
				return $lng->txt('trac_mode_objectives_info');

			case LP_MODE_COLLECTION:
				return $lng->txt('trac_mode_collection_info');

			case LP_MODE_SCORM:
				return $lng->txt('trac_mode_scorm_info');

			case LP_MODE_TEST_FINISHED:
				return $lng->txt('trac_mode_test_finished_info');

			case LP_MODE_TEST_PASSED:
				return $lng->txt('trac_mode_test_passed_info');

			case LP_MODE_EXERCISE_RETURNED:
				return $lng->txt('trac_mode_exercise_returned_info');

			case LP_MODE_SCORM_PACKAGE:
				return $lng->txt('trac_mode_scorm_package_info');
				
			case LP_MODE_EVENT:
				return $lng->txt('trac_mode_event_info');
		}
	}
}

?>