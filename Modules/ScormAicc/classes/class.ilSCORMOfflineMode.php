<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */
//require_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilSCORMOfflineMode
*
* Class for scorm offline player connection
*
* @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
* @version $Id: class.ilSCORMOfflineMode.php  $
*
* @ingroup ModulesScormAicc
*/
class ilSCORMOfflineMode
{
	var $type;
	var $obj_id;
	var $offlineMode;

	/**
	* Constructor
	*
	* @param	int		$a_id		Object ID
	* @access	public
	*/
	function __construct()
	{
		global $ilias;
		$this->ilias =& $ilias;
		$this->id = $_GET['ref_id'];
		$this->obj_id = ilObject::_lookupObjectId($_GET['ref_id']);
		include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
		$this->type = ilObjSAHSLearningModule::_lookupSubType($this->obj_id);
	}
	/**
	 * execute command
	 */
	// function &executeCommand()
	// {
		// global $ilAccess, $ilLog, $ilUser, $lng, $ilias;

		// $cmd = $_GET["cmd"];

		// if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		// {
			// $ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->WARNING);
		// }
// var_dump($cmd);
		// // switch($cmd){
		// // }
	// }

	function il2sop() {
		global $ilUser;
		$this->setOfflineMode("il2sop");
		header('Content-Type: text/javascript; charset=UTF-8');
		
		// $result = array(
			// 'lm' => $this->getDataForLm(),
			// 'schema' => array(), 
			// 'data' => array()
		// );

		include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
		$ob = new ilObjSAHSLearningModule($this->id);
		$module_version = $ob->getModuleVersion();

		if ($this->type == 'scorm2004') {
			include_once "./Modules/Scorm2004/classes/ilSCORM13Player.php";
			$ob2004 = new ilSCORM13Player();
			$init_data = $ob2004->getConfigForPlayer();
			$resources = json_decode($ob2004->getCPDataInit());
			$result = array(
				'lm' => array(
					ilObject::_lookupTitle($this->obj_id),
					ilObject::_lookupDescription($this->obj_id),
					$this->type,
					1,//active
					$init_data,
					$resources,
					"",
					$module_version,
					"" //offline_zip_created!!!!!!!!
				),
				'data' => $ob2004->getCMIData($ilUser->getID(), $this->obj_id)
			);
		}



		print(json_encode($result));
	}
	function getDataForLm() {
//		global  $ilias;
		include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
		$ob = new ilObjSAHSLearningModule($this->id);
		$module_version = $ob->getModuleVersion();

		if ($this->type == 'scorm2004') {
			include_once "./Modules/Scorm2004/classes/ilSCORM13Player.php";
			$ob2004 = new ilSCORM13Player();
			$init_data = $ob2004->getConfigForPlayer();
			$resources = json_decode($ob2004->getCPDataInit());
		}
		$result = array(
			ilObject::_lookupTitle($this->obj_id),
			ilObject::_lookupDescription($this->obj_id),
			$this->type,
			0,//active
			$init_data,
			$resources,
			"",
			$module_version,
			"" //offline_zip_created!!!!!!!!
		);
		return $result;
	}
	//offlineMode: offline, online, il2sop, sop2il
	function setOfflineMode($a_mode) {
		global $ilDB,$ilUser;
		$res = $ilDB->queryF('UPDATE sahs_user SET offline_mode=%s WHERE obj_id=%s AND user_id=%s',
			array('text','integer','integer'),
			array($a_mode, $this->obj_id,$ilUser->getId())
		);
		$this->offlineMode=$a_mode;
	}
	function getOfflineMode() {
		return $this->offlineMode;
	}
	
	private function read() {
		global $ilDB,$ilUser;
		$res = $ilDB->queryF('SELECT offline_mode FROM sahs_user WHERE obj_id=%s AND user_id=%s',
			array('integer','integer'),
			array($this->obj_id,$ilUser->getId())
		);
		while($row = $ilDB->fetchAssoc($res))
		{
			if ($row['offline_mode'] != null) {
				$this->offlineMode = $row['offline_mode'];
			} else {
				$this->offlineMode = "online";
			}
		}
	}
}