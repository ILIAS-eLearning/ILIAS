<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	function __construct($a_id = 0)
	{
		parent::__construct($a_id);
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

		$obj_set = $ilDB->queryF(
			'SELECT * FROM aicc_units WHERE obj_id = %s',
			array('integer'),
			array($this->getId())
		);
		while($obj_rec = $ilDB->fetchAssoc($obj_set))
		{
			$this->setAUType($obj_rec["c_type"]);
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
	}

	function create()
	{
		global $ilDB;
		
		parent::create();
	
		if($this->getMasteryScore() == NULL) $this->setMasteryScore(0);

		$ilDB->manipulateF('
		INSERT INTO aicc_units 
		(	obj_id, 
			c_type,
			command_line, 
			max_time_allowed, 
			time_limit_action,
			max_score, 
			core_vendor, 
			system_vendor, 
			file_name, 
			mastery_score,
			web_launch, 
			au_password
		) 
		VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s)',
		array('integer','text','text','time','text','float','text','text','text','integer','text','text'), 
		array(	$this->getId(),
				$this->getAUType(),
				$this->getCommand_line(),
				$this->getMaxTimeAllowed(),
				$this->getTimeLimitAction(),
				$this->getMaxScore(),
				$this->getCoreVendor(),
				$this->getSystemVendor(),
				$this->getFilename(),
				$this->getMasteryScore(),
				$this->getWebLaunch(),
				$this->getAUPassword())
		);
	}

	function update()
	{
		global $ilDB;
		
		parent::update();
		
		if($this->getMasteryScore() == NULL) $this->setMasteryScore(0);

		$ilDB->manipulateF('
			UPDATE aicc_units 
			SET c_type = %s,
				command_line = %s,
				max_time_allowed = %s,
				time_limit_action = %s,
				max_score = %s,
				core_vendor = %s,
				system_vendor = %s,
				file_name = %s,
				mastery_score = %s,
				web_launch = %s,
				au_password = %s		
			WHERE obj_id = %s',
		array('text','text','time','text','float','text','text','text','integer','text','text','integer'),
		array(	$this->getAUType(),
				$this->getCommand_line(),
				$this->getMaxTimeAllowed(),
				$this->getTimeLimitAction(),
				$this->getMaxScore(),
				$this->getCoreVendor(),
				$this->getSystemVendor(),
				$this->getFilename(),
				$this->getMasteryScore(),
				$this->getWebLaunch(),
				$this->getAUPassword(),
				$this->getId()) 
		);
	}

	function delete()
	{
		global $ilDB, $ilLog;

		parent::delete();

		$q_log = "DELETE FROM aicc_units WHERE obj_id =".$ilDB->quote($this->getId());
		$ilLog->write("SAHS Delete(Unit): ".$q_log);
		$ilDB->manipulateF(
			'DELETE FROM aicc_units WHERE obj_id = %s',
			array('integer'),
			array($this->getId()));

		$ilDB->manipulateF('
			DELETE FROM scorm_tracking 
			WHERE sco_id = %s
			AND obj_id =%s',
			array('integer','integer'),
			array($this->getId(),$this->getALMId())
		);
		
		// update learning progress status
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		ilLPStatusWrapper::_refreshStatus($this->getALMId());

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

		$track_set = $ilDB->queryF('
		SELECT lvalue, rvalue FROM scorm_tracking 
		WHERE sco_id = %s
		AND user_id = %s
		AND obj_id = %s',
		array('integer','integer','integer'),
		array($this->getId(), $a_user_id, $this->getALMId()));
		
		$trdata = array();
		while ($track_rec = $ilDB->fetchAssoc($track_set))
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
