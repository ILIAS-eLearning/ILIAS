<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilLPObjSettings
*
* @author Stefan Meyer <smeyer@databay.de>
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

define("LP_DEFAULT_VISITS",30);


class ilLPObjSettings
{
	var $db = null;

	var $obj_id = null;
	var $obj_type = null;
	var $obj_mode = null;
	var $visits = null;

	var $is_stored = false;

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

	 	$query = "INSERT INTO ut_lp_settings (obj_id,obj_type,mode,visits) ".
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

	function update()
	{
		global $ilDB;
		
		if(!$this->is_stored)
		{
			return $this->insert();
		}
		$query = "UPDATE ut_lp_settings SET mode = ".$ilDB->quote($this->getMode() ,'integer').", ".
			"visits = ".$ilDB->quote($this->getVisits() ,'integer')." ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId() ,'integer');
		$res = $ilDB->manipulate($query);
		$this->__read();
		return true;
	}

	function insert()
	{
		global $ilDB,$ilLog;
		
		$ilLog->logStack();
		
		$query = "INSERT INTO ut_lp_settings (obj_id,obj_type,mode,visits) ".
			"VALUES(".
			$ilDB->quote($this->getObjId() ,'integer').", ".
			$ilDB->quote($this->getObjType(),'text').", ".
			$ilDB->quote($this->getMode(),'integer').", ".
			$ilDB->quote(0, 'integer').
			")";
		$res = $ilDB->manipulate($query);
		$this->__read();
		return true;
	}


	// Static
	function _lookupVisits($a_obj_id)
	{
		global $ilDB;

		$query = "SELECT visits FROM ut_lp_settings ".
			"WHERE obj_id = ".$ilDB->quote($this->getObjId() ,'integer');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->visits;
		}
		return LP_DEFAULT_VISITS;
	}

	function _isContainer($a_mode)
	{
		return $a_mode == LP_MODE_COLLECTION or
			$a_mode == LP_MODE_SCORM or
			$a_mode == LP_MODE_OBJECTIVES or
			$a_mode == LP_MODE_MANUAL_BY_TUTOR;
	}
		

	function _delete($a_obj_id)
	{
		global $ilDB;

		$query = "DELETE FROM ut_lp_settings WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer');
		$res = $ilDB->manipulate($query);

		return true;
	}

	function _lookupMode($a_obj_id)
	{
		global $ilDB,$ilObjDataCache;

		if(ilLPObjSettings::_checkObjectives($a_obj_id))
		{
			return LP_MODE_OBJECTIVES;
		}
		if(ilLPObjSettings::_checkSCORMPreconditions($a_obj_id))
		{
			return LP_MODE_SCORM;
		}

		$query = "SELECT mode FROM ut_lp_settings ".
			"WHERE obj_id = ".$ilDB->quote($a_obj_id ,'integer');

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->mode;
		}
		
		// no db entry exists => return default mode by type
		return ilLPObjSettings::__getDefaultMode($a_obj_id,$ilObjDataCache->lookupType($a_obj_id));
	}

	function getValidModes()
	{
		global $lng;

		switch($this->obj_type)
		{
			case 'crs':
				if(ilLPObjSettings::_checkObjectives($this->getObjId()))
				{
					return array(LP_MODE_OBJECTIVES => $lng->txt('trac_mode_objectives'));
				}

				return array(LP_MODE_DEACTIVATED => $lng->txt('trac_mode_deactivated'),
							 LP_MODE_MANUAL_BY_TUTOR => $lng->txt('trac_mode_manual_by_tutor'),
							 LP_MODE_COLLECTION => $lng->txt('trac_mode_collection'));

				break;

			case 'dbk':
			case 'lm':
				return array(LP_MODE_MANUAL => $lng->txt('trac_mode_manual'),
							 LP_MODE_VISITS => $lng->txt('trac_mode_visits'),
							 LP_MODE_TLT => $lng->txt('trac_mode_tlt'),
							 LP_MODE_DEACTIVATED => $lng->txt('trac_mode_deactivated'));

			case 'htlm':
				return array(LP_MODE_DEACTIVATED => $lng->txt('trac_mode_deactivated'),
							 LP_MODE_MANUAL => $lng->txt('trac_mode_manual'));

			case 'sahs':
				include_once './Services/Tracking/classes/class.ilLPCollections.php';
				include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
				$subtype = ilObjSAHSLearningModule::_lookupSubType($this->getObjId());
				
				if ($subtype != "scorm2004")
				{
					if(ilLPObjSettings::_checkSCORMPreconditions($this->getObjId()))
					{
						return array(LP_MODE_SCORM => $lng->txt('trac_mode_scorm_aicc'));
					}
					if(ilLPCollections::_getCountPossibleSAHSItems($this->getObjId()))
					{
						return array(LP_MODE_DEACTIVATED => $lng->txt('trac_mode_deactivated'),
									 LP_MODE_SCORM => $lng->txt('trac_mode_scorm_aicc'));
					}
					return array(LP_MODE_DEACTIVATED => $lng->txt('trac_mode_deactivated'));
				}
				else
				{
					if(ilLPObjSettings::_checkSCORMPreconditions($this->getObjId()))
					{
						return array(LP_MODE_SCORM => $lng->txt('trac_mode_scorm_aicc'),
							LP_MODE_SCORM_PACKAGE => $lng->txt('trac_mode_scorm_package'));
					}
					if(ilLPCollections::_getCountPossibleSAHSItems($this->getObjId()))
					{
						return array(LP_MODE_DEACTIVATED => $lng->txt('trac_mode_deactivated'),
							LP_MODE_SCORM_PACKAGE => $lng->txt('trac_mode_scorm_package'),
							LP_MODE_SCORM => $lng->txt('trac_mode_scorm_aicc'));
					}
					return array(LP_MODE_DEACTIVATED => $lng->txt('trac_mode_deactivated'),
						LP_MODE_SCORM_PACKAGE => $lng->txt('trac_mode_scorm_package'));
				}
				break;

			case 'tst':
				return array(LP_MODE_TEST_FINISHED => $lng->txt('trac_mode_test_finished'),
							 LP_MODE_TEST_PASSED => $lng->txt('trac_mode_test_passed'),
							 LP_MODE_DEACTIVATED => $lng->txt('trac_mode_deactivated'));

			case 'exc':
				return array(LP_MODE_DEACTIVATED => $lng->txt('trac_mode_deactivated'),
							 LP_MODE_EXERCISE_RETURNED => $lng->txt('trac_mode_exercise_returned'));

			case 'grp':
				return array(LP_MODE_DEACTIVATED => $lng->txt('trac_mode_deactivated'),
							 LP_MODE_MANUAL_BY_TUTOR => $lng->txt('trac_mode_manual_by_tutor'),
							 LP_MODE_COLLECTION => $lng->txt('trac_mode_collection'));

			case 'fold':
				return array(LP_MODE_DEACTIVATED => $lng->txt('trac_mode_deactivated'),
							 LP_MODE_COLLECTION => $lng->txt('trac_mode_collection'));
			
			case 'sess':
				return array(LP_MODE_EVENT => $this->lng->txt('trac_mode_event'));

			default:
				return array();
		}
	}

	function _mode2Text($a_mode)
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
				

		}
	}
	
	/**
	 * get mode info text
	 *
	 * @access public
	 * @static
	 *
	 * @param int $mode
	 */
	public static function _mode2InfoText($a_mode)
	{
		global $lng;
		
		switch($a_mode)
		{
			case LP_MODE_DEACTIVATED:
				return $lng->txt('trac_mode_deactivated_info');

			case LP_MODE_TLT:
				return $lng->txt('trac_mode_tlt_info');

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
							 
				


	// Private
	function _checkObjectives($a_obj_id)
	{
		global $ilDB,$ilObjDataCache;

		// Return deactivate for course with objective view
		if($ilObjDataCache->lookupType($a_obj_id) == 'crs')
		{
			include_once 'Modules/Course/classes/class.ilObjCourse.php';

			if(ilObjCourse::_lookupViewMode($a_obj_id) == IL_CRS_VIEW_OBJECTIVE)
			{
				return true;
			}
		}
		return false;
	}
	
	function _checkSCORMPreconditions($a_obj_id)
	{
		global $ilObjDataCache;
		
		if($ilObjDataCache->lookupType($a_obj_id) != 'sahs')
		{
			return false;
		}
		include_once('classes/class.ilConditionHandler.php');
		if(count($conditions = ilConditionHandler::_getConditionsOfTrigger('sahs',$a_obj_id)))
		{
			return true;
		}
		return false;
	}
		


	function __read()
	{
		$res = $this->db->query("SELECT * FROM ut_lp_settings WHERE obj_id = ".
			$this->db->quote($this->obj_id ,'integer'));
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->is_stored = true;
			$this->obj_type = $row->obj_type;
			$this->obj_mode = $row->mode;
			$this->visits = $row->visits;

			if(ilLPObjSettings::_checkObjectives($this->obj_id))
			{
				$this->obj_mode = LP_MODE_OBJECTIVES;
			}
			if(ilLPObjSettings::_checkSCORMPreconditions($this->obj_id))
			{
				$this->obj_mode = LP_MODE_SCORM;
			}

			return true;
		}

		return false;
	}

	function __getDefaultMode($a_obj_id,$a_type)
	{
		global $ilDB;

		#$type = strlen($a_type) ? $a_type : $this->obj_type;

		switch($a_type)
		{
			case 'crs':
				// If objectives are enabled return deactivated
				if(ilLPObjSettings::_checkObjectives($a_obj_id))
				{
					return LP_MODE_OBJECTIVES;
				}
				return LP_MODE_MANUAL_BY_TUTOR;

			case 'dbk':
			case 'lm':
			case 'htlm':
				return LP_MODE_MANUAL;

			case 'sahs':
				return LP_MODE_DEACTIVATED;

			case 'dbk':
				return LP_MODE_MANUAL;

			case 'tst':
				return LP_MODE_TEST_PASSED;

			case 'exc':
				return LP_MODE_EXERCISE_RETURNED;

			case 'grp':
				return LP_MODE_DEACTIVATED;

			case 'fold':
				return LP_MODE_DEACTIVATED;
				
			case 'sess':
				return LP_MODE_EVENT;
					
			default:
				return LP_MODE_UNDEFINED;
		}
	}
}
?>