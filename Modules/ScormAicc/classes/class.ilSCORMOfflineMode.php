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
		$this->read();
	}

	function il2sop() {
		global $ilUser;
		$this->setOfflineMode("il2sop");
		header('Content-Type: text/javascript; charset=UTF-8');

		include_once "./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php";
		$ob = new ilObjSAHSLearningModule($this->id);
		$module_version = $ob->getModuleVersion();
		$sahs_user = $this->il2sopSahsUser();
		$support_mail = "";//TODO
		$scorm_version = "1.2";
		if ($this->type == "scorm2004") $scorm_version = "2004";

		if ($this->type == 'scorm2004') {
			include_once "./Modules/Scorm2004/classes/ilSCORM13Player.php";
			$ob2004 = new ilSCORM13Player();
			$init_data = $ob2004->getConfigForPlayer();
			$resources = json_decode($ob2004->getCPDataInit());
			$max_attempt = $ob2004->get_max_attempts();
			if ($max_attempt == null) $max_attempt = 0;
			$result = array(
				'client_data' => array(
					$support_mail
				),
				'user_data' => $this->il2sopUserData(),
				'lm' => array(
					ilObject::_lookupTitle($this->obj_id),
					ilObject::_lookupDescription($this->obj_id),
					$scorm_version,
					1,//active
					$init_data,
					$resources,
					"",
					$module_version,
					"", //offline_zip_created!!!!!!!!
					1,//learning_progress_enabled
					1,//certificate_enabled
					$max_attempt
				),
				'sahs_user' => $sahs_user,
				'cmi' => $ob2004->getCMIData($ilUser->getID(), $this->obj_id)
			);
		}
		print(json_encode($result));
	}
	
	function getClientIdSop() {
		$iliasDomain = substr(ILIAS_HTTP_PATH,7);
		if (substr($iliasDomain,0,1) == "\/") $iliasDomain = substr($iliasDomain,1);
		if (substr($iliasDomain,0,4) == "www.") $iliasDomain = substr($iliasDomain,4);
		return $iliasDomain.';'.CLIENT_ID;
	}
	
	function il2sopUserData() {
		global $ilUser;
		return array(
			$ilUser->getLogin(),
			"",
			$ilUser->getFirstname(),
			$ilUser->getLastname(),
			$ilUser->getUTitle(),
			$ilUser->getGender(),
			$ilUser->getID()
			);
	}
	function il2sopSahsUser() {
		global $ilDB,$ilUser;
		$package_attempts	= 0;
		$module_version		= 1;//if module_version in sop is different...
		$last_visited		= "";
		$first_access		= null;
		$last_access		= null;
		$last_status_change	= null;
		$total_time_sec		= null;
		$sco_total_time_sec	= 0;
		$status				= 0;
		$percentage_completed = 0;
		$user_data			= "";
		
		global $ilDB,$ilUser;
		$res = $ilDB->queryF('SELECT * FROM sahs_user WHERE obj_id=%s AND user_id=%s',
			array('integer','integer'),
			array($this->obj_id,$ilUser->getID())
		);
		while($row = $ilDB->fetchAssoc($res))
		{
			$module_version = $row['module_version'];
			$last_visited = $row['last_visited'];
			if ($row['last_access'] != null) {
				$last_access = strtotime($row['last_access'])*1000;//check Oracle!
			}
			$total_time_sec = $row['total_time_sec'];
			$sco_total_time_sec = $row['sco_total_time_sec'];
			$status = $row['status'];
			$percentage_completed = $row['percentage_completed'];
		}
		return array($package_attempts, $module_version, $last_visited, $first_access, $last_access, $last_status_change, $total_time_sec, $sco_total_time_sec, $status, $percentage_completed, $user_data);
	}

	function sop2il() {
		sleep(5);
		$in = file_get_contents("php://input");
		$ret = array('msg'=>array(),'err'=>array());
		
		if (!$in || $in == "") {
			$ret['err'][] = "no post data recieved";
			print(json_encode($ret));
			exit;
		}
		
		$obj = json_decode($in,true);
		if (!is_array($obj)) {
			$ret['err'][] = "invalid post data recieved";
			print(json_encode($ret));
			exit;
		}
		$ret['msg'][]  = "post data recieved";
		header('Content-Type: text/plain; charset=UTF-8');
		print json_encode($ret);
		exit;
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