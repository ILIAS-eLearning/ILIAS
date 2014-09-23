<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php';

/**
* Class ilSCORMTrackingItems
*
* @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
*
* @ingroup ModulesScormAicc
*/
class ilSCORMTrackingItems
{

	function scoTitlesForExportSelected() {
		global $ilDB;
		$scoTitles = array();

		$query = 'SELECT obj_id, title 
				FROM scorm_object
				WHERE slm_id = %s AND c_type = %s';
	 	$res = $ilDB->queryF(
			$query,
		 	array('integer', 'text'),
		 	array($this->getObjId(),'sit')
		);
		while($row = $ilDB->fetchAssoc($res))
		{
			$scoTitles[$row['obj_id']] = $row['title'];
		}
		return $scoTitles;
	}


	function markedLearningStatusForExportSelected($a_scos) {
		global $lng;
		include_once 'Services/Object/classes/class.ilObjectLP.php';
		$olp = ilObjectLP::getInstance($this->getObjId());
		$collection = $olp->getCollectionInstance();

		foreach($a_scos as $sco_id=>$value) {
			if ($collection && $collection->isAssignedEntry($sco_id)) $a_scos[$sco_id] = $lng->txt('yes');
			else $a_scos[$sco_id]=$lng->txt('no');
		}
		return $a_scos;
	}

	function userArrayForExportSelected($a_user = array()) {
		global $ilUser;
		$userArray = array();
		foreach($a_user as $user)
		{
			$userArray[$user] = array();
			//write export entry
			if(ilObject::_exists($user)  && ilObject::_lookUpType($user) == 'usr')
			{
				$e_user = new ilObjUser($user);
				$userArray[$user]["login"] = $e_user->getLogin();
				$userArray[$user]["user_name"] = $e_user->getLastname().", ".$e_user->getFirstname();
				$userArray[$user]["first_name"] = $e_user->getFirstname();
				$userArray[$user]["last_name"] = $e_user->getLastname();
				$userArray[$user]["email"] = "".$e_user->getEmail();
				$userArray[$user]["department"] = "".$e_user->getDepartment();
			}
		}
		return $userArray;
	}
	
	function exportSelectedCoreColumns($b_orderBySCO, $b_allowExportPrivacy) {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		// default fields
		$cols = array();
		$a_cols=explode(',',
			'lm_id,lm_title,sco_id,sco_marked_for_learning_progress,sco_title,user_id,login,name,email,department'
			.',lesson_status,credit,c_entry,c_exit,c_max,c_min,c_raw,session_time,total_time,c_timestamp,suspend_data,launch_data');
		$a_true=explode(',',"name,sco_title,lesson_status");
		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		return $cols;
	}

	function getScormTrackingValue($a_user = array(), $a_sco = array(), $a_empty = array(), $lvalue) {
		global $ilDB;
		
		$query = 'SELECT user_id, sco_id, rvalue '
			. 'FROM scorm_tracking ' 
			. 'WHERE obj_id = %s '
			. 'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
			. 'AND '.$ilDB->in('sco_id', $a_sco, false, 'integer') .' '
			. 'AND lvalue=%s';
		$res = $ilDB->queryF(
			$query,
			array('integer','text'),
			array($this->getObjId(),$lvalue)
		);
		while ($data = $ilDB->fetchAssoc($res)) {
			if(!is_null($data['rvalue'])) $a_empty[$data['user_id']][$data['sco_id']] = $data['rvalue'];
		}
		return $a_empty;
	}

	function exportSelectedCore($a_user = array(), $a_sco = array(), $b_orderBySCO=false, $allowExportPrivacy=false) {
		global $ilDB, $lng;
		$lng->loadLanguageModule("scormtrac");

		$returnData = array();

		$scoTitles = self::scoTitlesForExportSelected();

		$scoProgress = self::markedLearningStatusForExportSelected($scoTitles);

		if ($allowExportPrivacy == true) $userArray = self::userArrayForExportSelected($a_user);

		//data-arrays to fill for all users 
		$a_empty = array();
		for($i=0; $i<count($a_user); $i++) {
			$a_empty[$a_user[$i]] = array();
		}
		
		$dbdata = array();
		$query = 'SELECT user_id, sco_id, max(c_timestamp) as c_timestamp '
			. 'FROM scorm_tracking '
			. 'WHERE '.$ilDB->in('sco_id', $a_sco, false, 'integer') .' '
			. 'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
			. 'GROUP BY user_id, sco_id '
			. 'ORDER BY ';
			if ($b_orderBySCO) $query.='sco_id, user_id';
			else $query.='user_id, sco_id';
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$dbdata[] = $row;
			$a_empty[$row["user_id"]][$row["sco_id"]]="";
		}
		
		$a_lesson_status = self::getScormTrackingValue($a_user, $a_sco, $a_empty, 'cmi.core.lesson_status');
		$a_credit = self::getScormTrackingValue($a_user, $a_sco, $a_empty, 'cmi.core.credit');
		$a_c_entry = self::getScormTrackingValue($a_user, $a_sco, $a_empty, 'cmi.core.entry');
		$a_c_exit = self::getScormTrackingValue($a_user, $a_sco, $a_empty, 'cmi.core.exit');
		$a_c_max = self::getScormTrackingValue($a_user, $a_sco, $a_empty, 'cmi.core.score.max');
		$a_c_min = self::getScormTrackingValue($a_user, $a_sco, $a_empty, 'cmi.core.score.min');
		$a_c_raw = self::getScormTrackingValue($a_user, $a_sco, $a_empty, 'cmi.core.score.raw');
		$a_session_time = self::getScormTrackingValue($a_user, $a_sco, $a_empty, 'cmi.core.session_time');
		$a_total_time = self::getScormTrackingValue($a_user, $a_sco, $a_empty, 'cmi.core.total_time');
		$a_suspend_data = self::getScormTrackingValue($a_user, $a_sco, $a_empty, 'cmi.suspend_data');
		$a_launch_data = self::getScormTrackingValue($a_user, $a_sco, $a_empty, 'cmi.launch_data');

		foreach($dbdata as $data) {
			$data["lm_id"] = $this->getObjId();
			$data["lm_title"] = $this->lmTitle;
			$data["login"] = $lng->txt("display_not_allowed");
			$data["name"] = $lng->txt("display_not_allowed");
			$data["email"] = $lng->txt("display_not_allowed");
			$data["department"] = $lng->txt("display_not_allowed");
			if ($allowExportPrivacy == true) {
				$data["login"] = $userArray[$data["user_id"]]["login"];
				$data["name"] = $userArray[$data["user_id"]]["user_name"];
				$data["email"] = $userArray[$data["user_id"]]["email"];
				$data["department"] = $userArray[$data["user_id"]]["department"];
			}
			$data["sco_marked_for_learning_progress"] = "";//$scoProgress[$data["sco_id"]];
			$data["sco_title"] = $scoTitles[$data["sco_id"]];
			
			// $data["audio_captioning"] = "".$data["audio_captioning"];
			// $data["audio_level"] = "".$data["audio_level"];
			$data["lesson_status"] = $a_lesson_status[$data['user_id']][$data['sco_id']];
			$data["credit"] = $a_credit[$data['user_id']][$data['sco_id']];
			// $data["delivery_speed"] = "".$data["delivery_speed"];
			$data["c_entry"] = $a_c_entry[$data['user_id']][$data['sco_id']];
			$data["c_exit"] = $a_c_exit[$data['user_id']][$data['sco_id']];
			// $data["c_language"] = "".$data["c_language"];
			// $data["c_location"] = "".str_replace('"','',$data["c_location"]);
			// $data["c_mode"] = "".$data["c_mode"];
			$data["c_max"] = $a_c_max[$data['user_id']][$data['sco_id']];
			$data["c_min"] = $a_c_min[$data['user_id']][$data['sco_id']];
			$data["c_raw"] = $a_c_raw[$data['user_id']][$data['sco_id']];
			$data["session_time"] = $a_session_time[$data['user_id']][$data['sco_id']];
			// $data["session_time_seconds"] = "";
			// if ($data["session_time"] != "") $data["session_time_seconds"] = round(ilObjSCORM2004LearningModule::_ISODurationToCentisec($data["session_time"])/100);
			$data["total_time"] = $a_total_time[$data['user_id']][$data['sco_id']];
			// $data["total_time_seconds"] = "";
			// if ($data["total_time"] != "") $data["total_time_seconds"] = round(ilObjSCORM2004LearningModule::_ISODurationToCentisec($data["total_time"])/100);
			$data["c_timestamp"] = $data["c_timestamp"];//ilDatePresentation::formatDate(new ilDateTime($data["c_timestamp"],IL_CAL_UNIX));
			$data["suspend_data"] = $a_suspend_data[$data['user_id']][$data['sco_id']];
			$data["launch_data"] = $a_launch_data[$data['user_id']][$data['sco_id']];
			$returnData[]=$data;
		}
		
		return $returnData;
	}
	
	
	function exportSelectedSuccessColumns($b_allowExportPrivacy) {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		// default fields
		$cols = array();

		//use this: $this->userDataHeaderForExport();
		$a_cols=explode(',','LearningModuleId,LearningModuleTitle,LearningModuleVersion,'.self::userDataHeaderForExport()
			.',Status,Percentage,Attempts,existingSCOs,startedSCOs,completedSCOs,passedSCOs,roundedTotal_timeSeconds,offlineMode,Last Access');
		$s_user='UserId';
		if ($b_allowExportPrivacy == true) $s_user='First Name,Last Name';
		$a_true=explode(',',$s_user.",LearningModuleTitle,Status,Percentage,Attempts");

		// $a_cols=explode(',',
			// 'lm_id,lm_title,lm_version,user_id,login,name,email,department'
			// .',status,percentage,last_access,attempts,existingSCOs,startedSCOs,completedSCOs,passedSCOs,total_time_seconds');
		// $a_true=explode(',',"user_id,name,lm_title,status,percentage,last_access,attempts");
		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		return $cols;
	}

	function exportSelectedSuccess($a_user = array(), $allowExportPrivacy=false) {
		global $ilDB, $lng;
		$returnData=array();

		if ($allowExportPrivacy == true) $userArray = self::userArrayForExportSelected($a_user);

		$scoCounter = 0;
		$query = 'SELECT count(distinct(scorm_object.obj_id)) counter '
				.'FROM scorm_object, sc_item, sc_resource '
				.'WHERE scorm_object.slm_id = %s '
				.'AND scorm_object.obj_id = sc_item.obj_id '
				.'AND sc_item.identifierref = sc_resource.import_id '
				.'AND (sc_resource.scormtype = %s OR sc_resource.scormtype is null)';
	 	$res = $ilDB->queryF(
			$query,
		 	array('integer', 'text'),
		 	array($this->getObjId(),'sco')
		);		
		while($row = $ilDB->fetchAssoc($res))
		{
			$scoCounter = $row['counter'];
		}

		//data-arrays for all users 
		$u_startedSCO = array();
		$u_completedSCO = array();
		$u_passedSCO = array();
		for($i=0; $i<count($a_user); $i++) {
			$u_startedSCO[$a_user[$i]] = 0;
			$u_completedSCO[$a_user[$i]] = 0;
			$u_passedSCO[$a_user[$i]] = 0;
		}

		$query = 'SELECT user_id, count(distinct(SCO_ID)) counter '
			. 'FROM scorm_tracking ' 
			. 'WHERE obj_id = %s '
			. 'AND SCO_ID > 0 '
			. 'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
			. 'GROUP BY user_id';
		$res = $ilDB->queryF(
			$query,
			array('integer'),
			array($this->getObjId())
		);
		while ($data = $ilDB->fetchAssoc($res)) {
			$u_startedSCO[$data['user_id']] = $data['counter'];
		}

		$query = 'SELECT user_id, count(*) counter '
				.'FROM scorm_tracking ' 
				.'WHERE obj_id = %s AND lvalue = %s AND rvalue like %s '
				.'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
				.'GROUP BY user_id';
		$res = $ilDB->queryF(
			$query,
			array('integer','text','text'),
			array($this->getObjId(),'cmi.core.lesson_status','completed')
		);
		while ($data = $ilDB->fetchAssoc($res)) {
			$u_completedSCO[$data['user_id']] = $data['counter'];
		}

		$res = $ilDB->queryF(
			$query,
			array('integer','text','text'),
			array($this->getObjId(),'cmi.core.lesson_status','passed')
		);
		while ($data = $ilDB->fetchAssoc($res)) {
			$u_passedSCO[$data['user_id']] = $data['counter'];
		}

		$dbdata = array();

		$query = 'SELECT * FROM sahs_user WHERE obj_id = '.$ilDB->quote($this->getObjId(), 'integer')
			.' AND '.$ilDB->in('user_id', $a_user, false, 'integer')
			.' ORDER BY user_id';
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$dbdata[] = $row;
		}
		foreach($dbdata as $data) {
			$dat=array();
			$dat["LearningModuleId"] = $this->getObjId();
			$dat["LearningModuleTitle"] = $this->lmTitle;
			$dat["LearningModuleVersion"]=$data["module_version"];
			if ($allowExportPrivacy == true) {
				$dat["Login"] = $userArray[$data["user_id"]]["login"];
				$dat["First Name"] = $userArray[$data["user_id"]]["first_name"];
				$dat["Last Name"] = $userArray[$data["user_id"]]["last_name"];
				$dat["Email"] = $userArray[$data["user_id"]]["email"];
				$dat["Department"] = $userArray[$data["user_id"]]["department"];
			} else {
				$dat["UserId"]=$data["user_id"];
			}
			$dat["Status"]=$data["status"];
			$dat["Percentage"]=$data["percentage_completed"];
			$dat["Attempts"]=$data["package_attempts"];
			$dat["existingSCOs"]=$scoCounter;
			$dat["startedSCOs"]=$u_startedSCO[$data["user_id"]];
			$dat["completedSCOs"]=$u_completedSCO[$data["user_id"]];
			$dat["passedSCOs"]=$u_passedSCO[$data["user_id"]];
			$dat["roundedTotal_timeSeconds"]=$data["sco_total_time_sec"];
			if (is_null($data["offline_mode"])) $dat["offlineMode"]="";
			else $dat["offlineMode"]=$data["offline_mode"];
			$dat["Last Access"]=$data["last_access"];
			$returnData[]=$dat;
		}
		
		return $returnData;
		//CertificateDate?
	}

	
	// public function userDataForExport($a_user = array()) {
		// global $ilUser;
		// include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		// $privacy = ilPrivacySettings::_getInstance();
		// $allowExportPrivacy = $privacy->enabledExportSCORM();

		// $userData = array();
		// foreach($a_user as $user) {
			// if ($allowExportPrivacy == true) {
				// $userData[$user] = ";;;;";
				// //write export entry
				// if(ilObject::_exists($user)  && ilObject::_lookUpType($user) == 'usr') {
					// $e_user = new ilObjUser($user);
					// $userData[$user] = "\"". $e_user->getLogin() ."\""
						// . ";\"" . $e_user->getFirstname() ."\""
						// . ";\"" . $e_user->getLastname()."\""
						// . ";\"" . $e_user->getEmail() ."\""
						// . ";\"" . $e_user->getDepartment() ."\"";
				// }
			// } else {
				// $userData[$user] = ";";
				// if(ilObject::_exists($user)  && ilObject::_lookUpType($user) == 'usr') {
					// $userData[$user] = $user;
				// }
			// }
		// }
		// return $userData;
	// }

	public function userDataHeaderForExport() {
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();
		$allowExportPrivacy = $privacy->enabledExportSCORM();
		if ($allowExportPrivacy == true) return 'Login,First Name,Last Name,Email,Department';
		return 'UserId';
	}


}