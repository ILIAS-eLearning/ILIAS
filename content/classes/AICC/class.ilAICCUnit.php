<?php

require_once("content/classes/AICC/class.ilAICCObject.php");

class ilAICCUnit extends ilAICCObject
{

/**
* AICC Item
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilAICCObject
* @package content
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
		parent::read();

		$q = "SELECT * FROM aicc_units WHERE obj_id = '".$this->getId()."'";

		$obj_set = $this->ilias->db->query($q);
		$obj_rec = $obj_set->fetchRow(DB_FETCHMODE_ASSOC);
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
		parent::create();

		$q = "INSERT INTO aicc_units (obj_id, type, command_line, max_time_allowed, time_limit_action,
									max_score, core_vendor, system_vendor, file_name, mastery_score,
									web_launch, au_password) VALUES (";
		$q.="'".$this->getId()."', ";
		$q.="'".$this->prepForStore($this->getAUType())."', ";
		$q.="'".$this->prepForStore($this->getCommand_line())."', ";
		$q.="'".$this->prepForStore($this->getMaxTimeAllowed())."', ";
		$q.="'".$this->prepForStore($this->getTimeLimitAction())."', ";
		$q.="'".$this->prepForStore($this->getMaxScore())."', ";
		$q.="'".$this->prepForStore($this->getCoreVendor())."', ";
		$q.="'".$this->prepForStore($this->getSystemVendor())."', ";
		$q.="'".$this->prepForStore($this->getFilename())."', ";
		$q.="'".$this->prepForStore($this->getMasteryScore())."', ";
		$q.="'".$this->prepForStore($this->getWebLaunch())."', ";
		$q.="'".$this->prepForStore($this->getAUPassword())."')";

		$this->ilias->db->query($q);
	}

	function update()
	{
		parent::update();
		
		$q = "UPDATE aicc_units SET ";
		$q.="type='".$this->prepForStore($this->getAUType())."', ";
		$q.="command_line='".$this->prepForStore($this->getCommand_line())."', ";
		$q.="max_time_allowed='".$this->prepForStore($this->getMaxTimeAllowed())."', ";
		$q.="time_limit_action='".$this->prepForStore($this->getTimeLimitAction())."', ";
		$q.="max_score='".$this->prepForStore($this->getMaxScore())."', ";
		$q.="core_vendor='".$this->prepForStore($this->getCoreVendor())."', ";
		$q.="system_vendor='".$this->prepForStore($this->getSystemVendor())."', ";
		$q.="file_name='".$this->prepForStore($this->getFilename())."', ";
		$q.="mastery_score='".$this->prepForStore($this->getMasteryScore())."', ";
		$q.="web_launch='".$this->prepForStore($this->getWebLaunch())."', ";
		$q.="au_password='".$this->prepForStore($this->getAUPassword())."' ";		
		$q.="WHERE obj_id = '".$this->getId()."'";
		$this->ilias->db->query($q);
	}

	function delete()
	{
		global $ilDB;

		parent::delete();

		$q = "DELETE FROM aicc_units WHERE obj_id =".$ilDB->quote($this->getId());
		$ilDB->query($q);

		$q = "DELETE FROM scorm_tracking WHERE ".
			"sco_id = ".$ilDB->quote($this->getId());
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
			"sco_id = '".$this->getId()."' AND ".
			"user_id = '".$a_user_id."'";

		$track_set = $ilDB->query($q);
		$trdata = array();
		while ($track_rec = $track_set->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$trdata[$track_rec["lvalue"]] = $track_rec["rvalue"];
		}

		return $trdata;
	}
	
	function insertTrackData($a_lval, $a_rval, $a_obj_id)
	{
		require_once("content/classes/SCORM/class.ilObjSCORMTracking.php");
		ilObjSCORMTracking::_insertTrackData($this->getId(), $a_lval, $a_rval, $a_obj_id);
	}

}
?>
