<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

require_once("./Modules/ScormAicc/classes/AICC/class.ilAICCObject.php");

class ilAICCUnit extends ilAICCObject
{

/**
* AICC Item
*
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
	var $au_type;
	var $command_line;
	var $max_time_allowed;
	var $time_limit_action;
	var $max_score;
	var $core_vendor;
	var $system_vendor;
	var $file_name;
	var $mastery_score;
	var $web_launch;
	var $au_password;

	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function ilAICCUnit($a_id = 0)
	{
		parent::ilAICCObject($a_id);
		$this->setType("sau");
	}
	
	function getAUType()
	{
		return $this->au_type;
	}
	
	function setAUType($a_au_type)
	{
		$this->au_type = $a_au_type;
	}
	
	function getCommand_line()
	{
		return $this->command_line;
	}

	function setCommand_line($a_command_line)
	{
		$this->command_line = $a_command_line;
	}
	
	function getMaxTimeAllowed()
	{
		return $this->max_time_allowed;
	}
	
	function setMaxTimeAllowed($a_max_time_allowed)
	{
		$this->max_time_allowed = $a_max_time_allowed;
	}

	function getTimeLimitAction()
	{
		return $this->time_limit_action;
	}
	
	function setTimeLimitAction($a_time_limit_action)
	{
		$this->time_limit_action = $a_time_limit_action;
	}

	function getMaxScore()
	{
		return $this->max_score;
	}
	
	function setMaxScore($a_max_score)
	{
		$this->max_score = $a_max_score;
	}

	function getCoreVendor()
	{
		return $this->core_vendor;
	}
	
	function setCoreVendor($a_core_vendor)
	{
		$this->core_vendor = $a_core_vendor;
	}

	function getSystemVendor()
	{
		return $this->system_vendor;
	}
	
	function setSystemVendor($a_system_vendor)
	{
		$this->system_vendor = $a_system_vendor;
	}

	function getFilename()
	{
		return $this->file_name;
	}
	
	function setFilename($a_file_name)
	{
		$this->file_name = $a_file_name;
	}

	function getMasteryScore()
	{
		return $this->mastery_score;
	}
	
	function setMasteryScore($a_mastery_score)
	{
		$this->mastery_score = $a_mastery_score;
	}

	function getWebLaunch()
	{
		return $this->web_launch;
	}
	
	function setWebLaunch($a_web_launch)
	{
		$this->web_launch = $a_web_launch;
	}

	function getAUPassword()
	{
		return $this->au_password;
	}

	function setAUPassword($a_au_password)
	{
		$this->au_password = $a_au_password;
	}
	
	function read()
	{
		global $ilDB;
		
		parent::read();

		$q = "SELECT * FROM aicc_units WHERE obj_id = ".$ilDB->quote($this->getId());

		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(MDB2_FETCHMODE_ASSOC);
		$this->setAUType($obj_rec["type"]);
		$this->setCommand_line($obj_rec["command_line"]);
		$this->setMaxTimeAllowed($obj_rec["max_time_allowed"]);
		$this->setTimeLimitAction($obj_rec["time_limit_action"]);
		$this->setMaxScore($obj_rec["max_score"]);
		$this->setCoreVendor($obj_rec["core_vendor"]);
		$this->setSystemVendor($obj_rec["system_vendor"]);
		$this->setFilename($obj_rec["file_name"]);
		$this->setMasteryScore($obj_rec["mastery_score"]);
		$this->setWebLaunch($obj_rec["web_launch"]);
		$this->setAUPassword($obj_rec["au_password"]);
	}

	function create()
	{
		global $ilDB;
		
		parent::create();

		$q = "INSERT INTO aicc_units (obj_id, type, command_line, max_time_allowed, time_limit_action,
									max_score, core_vendor, system_vendor, file_name, mastery_score,
									web_launch, au_password) VALUES (";
		$q.=$ilDB->quote($this->getId()).", ";
		$q.=$ilDB->quote($this->getAUType()).", ";
		$q.=$ilDB->quote($this->getCommand_line()).", ";
		$q.=$ilDB->quote($this->getMaxTimeAllowed()).", ";
		$q.=$ilDB->quote($this->getTimeLimitAction()).", ";
		$q.=$ilDB->quote($this->getMaxScore()).", ";
		$q.=$ilDB->quote($this->getCoreVendor()).", ";
		$q.=$ilDB->quote($this->getSystemVendor()).", ";
		$q.=$ilDB->quote($this->getFilename()).", ";
		$q.=$ilDB->quote($this->getMasteryScore()).", ";
		$q.=$ilDB->quote($this->getWebLaunch()).", ";
		$q.=$ilDB->quote($this->getAUPassword()).")";

		$this->ilias->db->query($q);
	}

	function update()
	{
		global $ilDB;
		
		parent::update();
		
		$q = "UPDATE aicc_units SET ";
		$q.="type=".$ilDB->quote($this->getAUType()).", ";
		$q.="command_line=".$ilDB->quote($this->getCommand_line()).", ";
		$q.="max_time_allowed=".$ilDB->quote($this->getMaxTimeAllowed()).", ";
		$q.="time_limit_action=".$ilDB->quote($this->getTimeLimitAction()).", ";
		$q.="max_score=".$ilDB->quote($this->getMaxScore()).", ";
		$q.="core_vendor=".$ilDB->quote($this->getCoreVendor()).", ";
		$q.="system_vendor=".$ilDB->quote($this->getSystemVendor()).", ";
		$q.="file_name=".$ilDB->quote($this->getFilename()).", ";
		$q.="mastery_score=".$ilDB->quote($this->getMasteryScore()).", ";
		$q.="web_launch=".$ilDB->quote($this->getWebLaunch()).", ";
		$q.="au_password=".$ilDB->quote($this->getAUPassword())." ";		
		$q.="WHERE obj_id = ".$ilDB->quote($this->getId());
		$this->ilias->db->query($q);
	}

	function delete()
	{
		global $ilDB, $ilLog;

		parent::delete();

		$q = "DELETE FROM aicc_units WHERE obj_id =".$ilDB->quote($this->getId());
		$ilLog->write("SAHS Delete(Unit): ".$q);
		$ilDB->query($q);

		$q = "DELETE FROM scorm_tracking WHERE ".
			"sco_id = ".$ilDB->quote($this->getId()).
			" AND obj_id = ".$ilDB->quote($this->getALMId());
		$ilDB->query($q);

	}

	/**
	* get tracking data of specified or current user
	*
	*
	*/
	function getTrackingDataOfUser($a_user_id = 0)
	{
		global $ilDB, $ilUser;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		$q = "SELECT * FROM scorm_tracking WHERE ".
			"sco_id = ".$ilDB->quote($this->getId())." AND ".
			"user_id = ".$ilDB->quote($a_user_id).
			" AND obj_id = ".$ilDB->quote($this->getALMId());;

		$track_set = $ilDB->query($q);
		$trdata = array();
		while ($track_rec = $track_set->fetchRow(MDB2_FETCHMODE_ASSOC))
		{
			$trdata[$track_rec["lvalue"]] = $track_rec["rvalue"];
		}

		return $trdata;
	}
	
	function insertTrackData($a_lval, $a_rval, $a_obj_id)
	{
		require_once("./Modules/ScormAicc/classes/SCORM/class.ilObjSCORMTracking.php");
		ilObjSCORMTracking::_insertTrackData($this->getId(), $a_lval, $a_rval, $a_obj_id);
	}

}
?>
