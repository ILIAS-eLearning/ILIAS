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

	function userDataArrayForExport($user, $b_allowExportPrivacy=false) {
		$userArray = array();
		if ($b_allowExportPrivacy == false) {
			$userArray["user_id"]=$user;
		} else {
			global $ilUser;
			$userArray["login"] = "";
			$userArray["firstname"] = "";
			$userArray["lastname"] = "";
			$userArray["email"] = "";
			$userArray["department"] = "";
			if(ilObject::_exists($user)  && ilObject::_lookUpType($user) == 'usr') {
				$e_user = new ilObjUser($user);
				$userArray["login"] = $e_user->getLogin();
				$userArray["firstname"] = $e_user->getFirstname();
				$userArray["lastname"] = $e_user->getLastname();
				$userArray["email"] = "".$e_user->getEmail();
				$userArray["department"] = "".$e_user->getDepartment();
			}
		}
		return $userArray;
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

	function getScormTrackingValueForInteractionsOrObjectives($a_user = array(), $a_sco = array(), $lvalue, $counter, $topic) {
		global $ilDB;
		$a_return = array();
		$query = 'SELECT user_id, sco_id, rvalue '
			. 'FROM scorm_tracking ' 
			. 'WHERE obj_id = %s '
			. 'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
			. 'AND '.$ilDB->in('sco_id', $a_sco, false, 'integer') .' '
			. 'AND lvalue = %s';
		$res = $ilDB->queryF(
			$query,
			array('integer','text'),
			array($this->getObjId(),'cmi.'.$topic.'.'.$counter.'.'.$lvalue)
		);
		while ($data = $ilDB->fetchAssoc($res)) {
			if(!is_null($data['rvalue'])) $a_return[''.$data['user_id'].'-'.$data['sco_id'].'-'.$counter] = $data['rvalue'];
		}
		return $a_return;
	}

	function exportSelectedCoreColumns($b_orderBySCO, $b_allowExportPrivacy) {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		// default fields
		$cols = array();
		$udh=self::userDataHeaderForExport();
		$a_cols=explode(',',
			'lm_id,lm_title,sco_id,sco_marked_for_learning_progress,sco_title,'.$udh["cols"]
			.',lesson_status,credit,c_entry,c_exit,c_max,c_min,c_raw,session_time,total_time,c_timestamp,suspend_data,launch_data');
		$a_true=explode(',',$udh["default"].",sco_title,lesson_status");
		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		return $cols;
	}

	function exportSelectedCore($a_user = array(), $a_sco = array(), $b_orderBySCO=false, $allowExportPrivacy=false) {
		global $ilDB, $lng;
		$lng->loadLanguageModule("scormtrac");

		$returnData = array();

		$scoTitles = self::scoTitlesForExportSelected();

		$scoProgress = self::markedLearningStatusForExportSelected($scoTitles);

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

			$data=array_merge($data,self::userDataArrayForExport($data["user_id"], $allowExportPrivacy));

			$data["sco_marked_for_learning_progress"] = $scoProgress[$data["sco_id"]];
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
	
	function exportSelectedInteractionsColumns() {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		$cols = array();
		$udh=self::userDataHeaderForExport();
		$a_cols=explode(',',
			'lm_id,lm_title,sco_id,sco_marked_for_learning_progress,sco_title,'.$udh["cols"]
			.',counter,id,weighting,type,result,student_response,latency,time,c_timestamp');//,latency_seconds
		$a_true=explode(',',$udh["default"].",sco_title,id,result,student_response");
		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		return $cols;
	}

	function exportSelectedInteractions($a_user = array(), $a_sco = array(), $b_orderBySCO=false, $allowExportPrivacy=false) {
		global $ilDB;

		$returnData = array();

		$scoTitles = self::scoTitlesForExportSelected();

		$scoProgress = self::markedLearningStatusForExportSelected($scoTitles);

		$dbdata = array();

		$interactionsCounter = array();
		$prevcounter = -1;

		$query = 'SELECT user_id, sco_id, lvalue, c_timestamp '
			. 'FROM scorm_tracking '
			. 'WHERE obj_id = %s AND '.$ilDB->in('sco_id', $a_sco, false, 'integer') .' '
			. 'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
			. 'AND left(lvalue,17) = %s '
			. 'ORDER BY ';
			if ($b_orderBySCO) $query.='sco_id, user_id, lvalue';
			else $query.='user_id, sco_id, lvalue';
		$res = $ilDB->queryF(
			$query,
			array('integer','text'),
			array($this->getObjId(),'cmi.interactions.'));

		while($row = $ilDB->fetchAssoc($res))
		{
			$tmpar = explode('.',$row["lvalue"]);
			$tmpcounter = $tmpar[2];
			if (in_array($tmpcounter,$interactionsCounter) == false) $interactionsCounter[] = $tmpcounter;
			if ($tmpcounter != $prevcounter) {
				$tmpar = array();
				$tmpar["user_id"] = $row["user_id"];
				$tmpar["sco_id"] = $row["sco_id"];
				$tmpar["counter"] = $tmpcounter;
				$tmpar["id"] = "";
				$tmpar["weighting"] = "";
				$tmpar["type"] = "";
				$tmpar["result"] = "";
				$tmpar["student_response"] = "";
				$tmpar["latency"] = "";
				$tmpar["time"] = "";
				$tmpar["c_timestamp"] = $row["c_timestamp"];
				$dbdata[] = $tmpar;
				$prevcounter = $tmpcounter;
			}
		}
//		id,weighting,type,result,student_response,latency,time

		$a_id = array();
		$a_weighting = array();
		$a_type = array();
		$a_result = array();
		$a_student_response = array();
		$a_latency = array();
		$a_time = array();
		for($i=0;$i<count($interactionsCounter);$i++) {
			$a_id=array_merge($a_id,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'id', $interactionsCounter[$i],'interactions'));
			$a_weighting=array_merge($a_weighting,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'weighting', $interactionsCounter[$i],'interactions'));
			$a_type=array_merge($a_type,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'type', $interactionsCounter[$i],'interactions'));
			$a_result=array_merge($a_result,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'result', $interactionsCounter[$i],'interactions'));
			$a_student_response=array_merge($a_student_response,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'student_response', $interactionsCounter[$i],'interactions'));
			$a_latency=array_merge($a_latency,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'latency', $interactionsCounter[$i],'interactions'));
			$a_time=array_merge($a_time,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'time', $interactionsCounter[$i],'interactions'));
		}
		foreach($dbdata as $data) {
			$data["lm_id"] = $this->getObjId();
			$data["lm_title"] = $this->lmTitle;

			$data=array_merge($data,self::userDataArrayForExport($data["user_id"], $allowExportPrivacy));

			$data["sco_marked_for_learning_progress"] = $scoProgress[$data["sco_id"]];
			$data["sco_title"] = $scoTitles[$data["sco_id"]];
			
			$combinedId = ''.$data["user_id"].'-'.$data["sco_id"].'-'.$data["counter"];
			if (array_key_exists($combinedId,$a_id)) $data["id"] = $a_id[$combinedId];
			if (array_key_exists($combinedId,$a_weighting)) $data["weighting"] = $a_weighting[$combinedId];
			if (array_key_exists($combinedId,$a_type)) $data["type"] = $a_type[$combinedId];
			if (array_key_exists($combinedId,$a_result)) $data["result"] = $a_result[$combinedId];
			if (array_key_exists($combinedId,$a_student_response)) $data["student_response"] = $a_student_response[$combinedId];
			if (array_key_exists($combinedId,$a_latency)) $data["latency"] = $a_latency[$combinedId];
			if (array_key_exists($combinedId,$a_time)) $data["time"] = $a_time[$combinedId];

			//$data["c_timestamp"] = $data["c_timestamp"];//ilDatePresentation::formatDate(new ilDateTime($data["c_timestamp"],IL_CAL_UNIX));
			$returnData[]=$data;
		}

//		var_dump($returnData);
		return $returnData;
	}
	/*
	*/
	function exportSelectedObjectivesColumns() {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		$cols = array();
		$udh=self::userDataHeaderForExport();
		$a_cols=explode(',',
			'lm_id,lm_title,sco_id,sco_marked_for_learning_progress,sco_title,'.$udh["cols"]
			.',counter,id,c_max,c_min,c_raw,ostatus,c_timestamp');
		$a_true=explode(',',$udh["default"].",sco_title,id,c_raw,ostatus");
		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		return $cols;
	}

	function exportSelectedObjectives($a_user = array(), $a_sco = array(), $b_orderBySCO=false, $allowExportPrivacy=false) {
		global $ilDB;

		$returnData = array();

		$scoTitles = self::scoTitlesForExportSelected();

		$scoProgress = self::markedLearningStatusForExportSelected($scoTitles);

		$dbdata = array();

		$objectivesCounter = array();
		$prevcounter = -1;

		$query = 'SELECT user_id, sco_id, lvalue, c_timestamp '
			. 'FROM scorm_tracking '
			. 'WHERE obj_id = %s AND '.$ilDB->in('sco_id', $a_sco, false, 'integer') .' '
			. 'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
			. 'AND left(lvalue,15) = %s '
			. 'ORDER BY ';
			if ($b_orderBySCO) $query.='sco_id, user_id, lvalue';
			else $query.='user_id, sco_id, lvalue';
		$res = $ilDB->queryF(
			$query,
			array('integer','text'),
			array($this->getObjId(),'cmi.objectives.'));

		while($row = $ilDB->fetchAssoc($res))
		{
			$tmpar = explode('.',$row["lvalue"]);
			$tmpcounter = $tmpar[2];
			if (in_array($tmpcounter,$objectivesCounter) == false) $objectivesCounter[] = $tmpcounter;
			if ($tmpcounter != $prevcounter) {
				$tmpar = array();
				$tmpar["user_id"] = $row["user_id"];
				$tmpar["sco_id"] = $row["sco_id"];
				$tmpar["counter"] = $tmpcounter;
				$tmpar["id"] = "";
				$tmpar["c_max"] = "";
				$tmpar["c_min"] = "";
				$tmpar["c_raw"] = "";
				$tmpar["ostatus"] = "";
				$tmpar["c_timestamp"] = $row["c_timestamp"];
				$dbdata[] = $tmpar;
				$prevcounter = $tmpcounter;
			}
		}
		$a_id = array();
		$a_c_max = array();
		$a_c_min = array();
		$a_c_raw = array();
		$a_status = array();
		for($i=0;$i<count($objectivesCounter);$i++) {
			$a_id=array_merge($a_id,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'id', $objectivesCounter[$i],'objectives'));
			$a_c_max=array_merge($a_c_max,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'score.max', $objectivesCounter[$i],'objectives'));
			$a_c_min=array_merge($a_c_min,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'score.min', $objectivesCounter[$i],'objectives'));
			$a_c_raw=array_merge($a_c_raw,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'score.raw', $objectivesCounter[$i],'objectives'));
			$a_status=array_merge($a_status,self::getScormTrackingValueForInteractionsOrObjectives($a_user, $a_sco, 'status', $objectivesCounter[$i],'objectives'));
		}
		foreach($dbdata as $data) {
			$data["lm_id"] = $this->getObjId();
			$data["lm_title"] = $this->lmTitle;

			$data=array_merge($data,self::userDataArrayForExport($data["user_id"], $allowExportPrivacy));

			$data["sco_marked_for_learning_progress"] = $scoProgress[$data["sco_id"]];
			$data["sco_title"] = $scoTitles[$data["sco_id"]];
			
			$combinedId = ''.$data["user_id"].'-'.$data["sco_id"].'-'.$data["counter"];
			if (array_key_exists($combinedId,$a_id)) $data["id"] = $a_id[$combinedId];
			if (array_key_exists($combinedId,$a_c_max)) $data["c_max"] = $a_c_max[$combinedId];
			if (array_key_exists($combinedId,$a_c_min)) $data["c_min"] = $a_c_min[$combinedId];
			if (array_key_exists($combinedId,$a_c_raw)) $data["c_raw"] = $a_c_raw[$combinedId];
			if (array_key_exists($combinedId,$a_status)) $data["ostatus"] = $a_status[$combinedId];

			//$data["c_timestamp"] = $data["c_timestamp"];//ilDatePresentation::formatDate(new ilDateTime($data["c_timestamp"],IL_CAL_UNIX));
			$returnData[]=$data;
		}

//		var_dump($returnData);
		return $returnData;
	}

	function exportSelectedSuccessColumns() {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		// default fields
		$cols = array();

		$udh=self::userDataHeaderForExport();
		$a_cols=explode(',','LearningModuleId,LearningModuleTitle,LearningModuleVersion,'.$udh["cols"]
			.',status,Percentage,Attempts,existingSCOs,startedSCOs,completedSCOs,passedSCOs,roundedTotal_timeSeconds,offline_mode,last_access');
		$a_true=explode(',',$udh["default"].",LearningModuleTitle,status,Percentage,Attempts");

		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		return $cols;
	}

	function exportSelectedSuccessRows($a_user = array(), $allowExportPrivacy=false, $dbdata = array(), $scoCounter, $u_startedSCO, $u_completedSCO, $u_passedSCO) {
		$returnData=array();
		foreach($dbdata as $data) {
			$dat=array();
			$dat["LearningModuleId"] = $this->getObjId();
			$dat["LearningModuleTitle"] = $this->lmTitle;
			$dat["LearningModuleVersion"]=$data["module_version"];

			$dat=array_merge($dat,self::userDataArrayForExport($data["user_id"], $allowExportPrivacy));

			$dat["status"]=$data["status"];
			$dat["Percentage"]=$data["percentage_completed"];
			$dat["Attempts"]=$data["package_attempts"];
			$dat["existingSCOs"]=$scoCounter;
			$dat["startedSCOs"]=$u_startedSCO[$data["user_id"]];
			$dat["completedSCOs"]=$u_completedSCO[$data["user_id"]];
			$dat["passedSCOs"]=$u_passedSCO[$data["user_id"]];
			$dat["roundedTotal_timeSeconds"]=$data["sco_total_time_sec"];
			if (is_null($data["offline_mode"])) $dat["offline_mode"]="";
			else $dat["offline_mode"]=$data["offline_mode"];
			$dat["last_access"]=$data["last_access"];
			$returnData[]=$dat;
		}
		return $returnData;
	}

	function exportSelectedSuccess($a_user = array(), $allowExportPrivacy=false) {
		global $ilDB;

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
		return self::exportSelectedSuccessRows($a_user, $allowExportPrivacy, $dbdata, $scoCounter, $u_startedSCO, $u_completedSCO, $u_passedSCO);
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
		$returnData = array();
		if ($allowExportPrivacy == true) {
			$returnData["cols"] = 'login,firstname,lastname,email,department';
			$returnData["default"] = 'firstname,lastname';
		} else {
			$returnData["cols"] = 'user_id';
			$returnData["default"] = 'user_id';
		}
		return $returnData;
	}


}