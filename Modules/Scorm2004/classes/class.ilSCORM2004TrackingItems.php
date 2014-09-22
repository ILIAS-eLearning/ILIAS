<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php';

/**
* Class ilSCORM2004TrackingItems
*
* @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004TrackingItems
{

	function scoTitlesForExportSelected() {
		global $ilDB;
		$scoTitles = array();
		$query = 'SELECT cp_item.cp_node_id, cp_item.title '
			. 'FROM cp_item, cmi_node, cp_node '
			. 'WHERE cp_node.slm_id = %s '
			. 'AND cp_item.cp_node_id = cmi_node.cp_node_id '
			. 'AND cp_node.cp_node_id = cmi_node.cp_node_id '
			. 'GROUP BY cp_item.cp_node_id, cp_item.title';
	 	$res = $ilDB->queryF(
			$query,
		 	array('integer'),
		 	array($this->getObjId())
		);
		while($row = $ilDB->fetchAssoc($res))
		{
			$scoTitles[$row['cp_node_id']] = $row['title'];
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
			'lm_id,lm_title,cp_node_id,sco_marked_for_learning_progress,sco_title,user_id,login,name,email,department'
			.',audio_captioning,audio_level,completion_status,completion_threshold,credit,delivery_speed'
			.',c_entry,c_exit,c_language,c_location,c_mode,progress_measure,c_max,c_min,c_raw,scaled'
			.',scaled_passing_score,session_time,session_time_seconds,success_status,total_time,total_time_seconds,c_timestamp,suspend_data,launch_data');
		$a_true=explode(',',"name,sco_title,success_status,completion_status");
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

		if ($allowExportPrivacy == true) $userArray = self::userArrayForExportSelected($a_user);

		$dbdata = array();
		$query = 'SELECT user_id, cp_node_id, '
			. 'audio_captioning, audio_level, completion_status, completion_threshold, credit, delivery_speed, '
			. 'c_entry, c_exit, c_language, location as c_location, c_mode, progress_measure, c_max, c_min, c_raw, scaled, '
			. 'scaled_passing_score, session_time, success_status, total_time, c_timestamp, suspend_data, launch_data '
			. 'FROM cmi_node '
			. 'WHERE '.$ilDB->in('cp_node_id', $a_sco, false, 'integer') .' '
			. 'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
			. 'ORDER BY ';
			if ($b_orderBySCO) $query.='cp_node_id, user_id';
			else $query.='user_id, cp_node_id';
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$dbdata[] = $row;
		}
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
			$data["sco_marked_for_learning_progress"] = $scoProgress[$data["cp_node_id"]];
			$data["sco_title"] = $scoTitles[$data["cp_node_id"]];
			$data["audio_captioning"] = "".$data["audio_captioning"];
			$data["audio_level"] = "".$data["audio_level"];
			$data["completion_status"] = "".$data["completion_status"];
			$data["completion_threshold"] = "".$data["completion_threshold"];
			$data["credit"] = "".$data["credit"];
			$data["delivery_speed"] = "".$data["delivery_speed"];
			$data["c_entry"] = "".$data["c_entry"];
			$data["c_exit"] = "".$data["c_exit"];
			$data["c_language"] = "".$data["c_language"];
			$data["c_location"] = "".str_replace('"','',$data["c_location"]);
			$data["c_mode"] = "".$data["c_mode"];
			$data["progress_measure"] = "".$data["progress_measure"];
			$data["c_max"] = "".$data["c_max"];
			$data["c_min"] = "".$data["c_min"];
			$data["c_raw"] = "".$data["c_raw"];
			$data["scaled"] = "".$data["scaled"];//$data["scaled"]*100)
			$data["scaled_passing_score"] = "".$data["scaled_passing_score"];
			$data["session_time"] = "".$data["session_time"];
			$data["session_time_seconds"] = "";
			if ($data["session_time"] != "") $data["session_time_seconds"] = round(ilObjSCORM2004LearningModule::_ISODurationToCentisec($data["session_time"])/100);
			$data["success_status"] = "".$data["success_status"];
			$data["total_time"] = "".$data["total_time"];
			$data["total_time_seconds"] = "";
			if ($data["total_time"] != "") $data["total_time_seconds"] = round(ilObjSCORM2004LearningModule::_ISODurationToCentisec($data["total_time"])/100);
			$data["c_timestamp"] = $data["c_timestamp"];//ilDatePresentation::formatDate(new ilDateTime($data["c_timestamp"],IL_CAL_UNIX));
			$data["suspend_data"] = "".$data["suspend_data"];
			$data["launch_data"] = "".$data["launch_data"];
				// if ($data["success_status"]!="" && $data["success_status"]!="unknown") {
					// $status = $data["success_status"];
				// } else {
					// if ($data["completion_status"]=="") {
						// $status="unknown";
					// } else {
						// $status = $data["completion_status"];
					// }
				// }
			$returnData[]=$data;
		}
		
		return $returnData;
	}
	
	function exportSelectedInteractionsColumns($b_orderBySCO, $b_allowExportPrivacy) {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		$cols = array();
		$a_cols=explode(',',
			'lm_id,lm_title,cp_node_id,sco_marked_for_learning_progress,sco_title,user_id,login,name,email,department'
			.',id,description,weighting,c_type,result,latency,latency_seconds,c_timestamp,learner_response');
		$a_true=explode(',',"name,sco_title,description,result,learner_response");//note for trunk: id instead of description
		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		return $cols;
	}

	function exportSelectedInteractions($a_user = array(), $a_sco = array(), $b_orderBySCO=false, $allowExportPrivacy=false) {
		global $ilDB, $lng;
		$lng->loadLanguageModule("scormtrac");

		$returnData = array();

		$scoTitles = self::scoTitlesForExportSelected();

		$scoProgress = self::markedLearningStatusForExportSelected($scoTitles);

		if ($allowExportPrivacy == true) $userArray = self::userArrayForExportSelected($a_user);

		$dbdata = array();
		$query = 'SELECT cmi_node.user_id, cmi_node.cp_node_id,
				cmi_interaction.cmi_interaction_id,
				cmi_interaction.id, 
				cmi_interaction.description, 
				cmi_interaction.weighting, 
				cmi_interaction.c_type, 
				cmi_interaction.result, 
				cmi_interaction.latency, 
				cmi_interaction.c_timestamp, 
				cmi_interaction.learner_response, 
				cmi_interaction.cmi_interaction_id, 
				cmi_interaction.cmi_node_id 
				FROM cmi_interaction, cmi_node 
				WHERE '.$ilDB->in('cp_node_id', $a_sco, false, 'integer') .' 
				AND '.$ilDB->in('cmi_node.user_id', $a_user, false, 'integer') .'
				AND cmi_node.cmi_node_id = cmi_interaction.cmi_node_id
				ORDER BY ';
			if ($b_orderBySCO) $query.='cmi_node.cp_node_id, cmi_node.user_id';
			else $query.='cmi_node.user_id, cmi_node.cp_node_id';
			$query.=', cmi_interaction.cmi_interaction_id, cmi_interaction.cmi_node_id';
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$dbdata[] = $row;
		}
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
			$data["sco_marked_for_learning_progress"] = $scoProgress[$data["cp_node_id"]];
			$data["sco_title"] = $scoTitles[$data["cp_node_id"]];
			$data["description"] = "".$data["description"]; 
			$data["weighting"] = "".$data["weighting"];
			$data["c_type"] = "".$data["c_type"];
			$data["result"] = "".$data["result"];
			$data["latency"] = "".$data["latency"];
			$data["latency_seconds"] = "";
			if ($data["latency"]!="") $data["latency_seconds"] = round(ilObjSCORM2004LearningModule::_ISODurationToCentisec($data["latency"])/100);
			$data["c_timestamp"] = "".$data["c_timestamp"];
			$data["learner_response"] = "".str_replace('"','',$data["learner_response"]);
			$returnData[]=$data;
		}
//		var_dump($returnData);
		return $returnData;
	}
	
	function tracInteractionItemColumns($b_orderBySCO, $b_allowExportPrivacy) {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		$cols = array();
		$a_cols=explode(',',
			'lm_id,lm_title,cp_node_id,sco_marked_for_learning_progress,sco_title'
			.',id,description,counter_all'
			.',counter_correct,counter_correct_percent'
			.',counter_incorrect,counter_incorrect_percent'
			.',counter_other,counter_other_percent');
		$a_true=explode(',',"sco_title,description,counter_correct,counter_incorrect");
		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		return $cols;
	}

	function tracInteractionItem($a_user = array(), $a_sco = array(), $b_orderBySCO=false, $allowExportPrivacy=false) {
		global $ilDB, $lng;
		$lng->loadLanguageModule("scormtrac");

		$returnData = array();

		$scoTitles = self::scoTitlesForExportSelected();

		$scoProgress = self::markedLearningStatusForExportSelected($scoTitles);

		if ($allowExportPrivacy == true) $userArray = self::userArrayForExportSelected($a_user);


		$a_correct = array();
		$a_incorrect = array();
		$query = 'SELECT cmi_node.cp_node_id, cmi_interaction.id, count(*) as counter
				FROM cmi_interaction, cmi_node 
				WHERE '.$ilDB->in('cp_node_id', $a_sco, false, 'integer') .' 
				AND '.$ilDB->in('cmi_node.user_id', $a_user, false, 'integer') .'
				AND cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
				AND cmi_interaction.result = %s 
				GROUP BY cmi_node.cp_node_id,cmi_interaction.id';

		$res = $ilDB->queryF($query,array('text'),array('correct'));
		while($row = $ilDB->fetchAssoc($res))
		{
			$a_correct[$row['cp_node_id'].':'.$row['id']] = $row['counter'];
		}

		$res = $ilDB->queryF($query,array('text'),array('incorrect'));
		while($row = $ilDB->fetchAssoc($res))
		{
			$a_incorrect[$row['cp_node_id'].':'.$row['id']] = $row['counter'];
		}

		$dbdata = array();
		$query = 'SELECT cmi_node.cp_node_id, cmi_interaction.id, cmi_interaction.description, count(*) as counter_all
				FROM cmi_interaction, cmi_node 
				WHERE '.$ilDB->in('cp_node_id', $a_sco, false, 'integer') .' 
				AND '.$ilDB->in('cmi_node.user_id', $a_user, false, 'integer') .'
				AND cmi_node.cmi_node_id = cmi_interaction.cmi_node_id
				GROUP BY cmi_node.cp_node_id,cmi_interaction.id,cmi_interaction.description';
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$dbdata[] = $row;
		}
		foreach($dbdata as $data) {
			$skey = $data["cp_node_id"].':'.$data["id"];
			$all = $data["counter_all"];
			$correct = 0;
			if($a_correct[$skey] != null) $correct = $a_correct[$skey];
			$incorrect = 0;
			if($a_incorrect[$skey] != null) $incorrect = $a_incorrect[$skey];
			$other = $all-($correct+$incorrect);
			$data["lm_id"] = $this->getObjId();
			$data["lm_title"] = $this->lmTitle;
			$data["sco_marked_for_learning_progress"] = $scoProgress[$data["cp_node_id"]];
			$data["sco_title"] = $scoTitles[$data["cp_node_id"]];
//			$data["id"] = "".$data["id"];
			$data["description"] = "".$data["description"];
//			$data["counter_all"] = $data["counter"];
			$data["counter_correct"] = $correct;
			$data["counter_correct_percent"] = $correct*100/$all;
			$data["counter_incorrect"] = $incorrect;
			$data["counter_incorrect_percent"] = $incorrect*100/$all;
			$data["counter_other"] = $other;
			$data["counter_other_percent"] = $other*100/$all;
			$returnData[]=$data;
		}
		return $returnData;
	}
	
	function tracInteractionUserColumns($b_orderBySCO, $b_allowExportPrivacy) {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		// default fields
		$cols = array();
		$a_cols=explode(',',
			'lm_id,lm_title,cp_node_id,sco_marked_for_learning_progress,sco_title,user_id,login,name,email,department'
			.',counter_i_correct,counter_i_correct_percent'
			.',counter_i_incorrect,counter_i_incorrect_percent'
			.',counter_i_other,counter_i_other_percent'
			.',audio_captioning,audio_level,completion_status,completion_threshold,credit,delivery_speed'
			.',c_entry,c_exit,c_language,c_location,c_mode,progress_measure,c_max,c_min,c_raw,scaled'
			.',scaled_passing_score,session_time,session_time_seconds,success_status,total_time,total_time_seconds,c_timestamp,suspend_data,launch_data');
		$a_true=explode(',','name,sco_title'
			.',counter_i_correct,counter_i_correct_percent'
			.',counter_i_incorrect,counter_i_incorrect_percent'
			.',counter_i_other,counter_i_other_percent'
			.',c_raw,scaled');
		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		return $cols;
	}

	function tracInteractionUser($a_user = array(), $a_sco = array(), $b_orderBySCO=false, $allowExportPrivacy=false) {
		global $ilDB, $lng;
		$lng->loadLanguageModule("scormtrac");

		$returnData = array();

		$scoTitles = self::scoTitlesForExportSelected();

		$scoProgress = self::markedLearningStatusForExportSelected($scoTitles);

		if ($allowExportPrivacy == true) $userArray = self::userArrayForExportSelected($a_user);


		$a_correct = array();
		$a_incorrect = array();
		$a_other = array();
		$query = 'SELECT cmi_node.user_id, cmi_node.cp_node_id, count(*) as counter
				FROM cmi_interaction, cmi_node 
				WHERE '.$ilDB->in('cp_node_id', $a_sco, false, 'integer') .' 
				AND '.$ilDB->in('cmi_node.user_id', $a_user, false, 'integer') .'
				AND cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
				AND cmi_interaction.result = %s 
				GROUP BY cmi_node.user_id,cmi_node.cp_node_id';

		$res = $ilDB->queryF($query,array('text'),array('correct'));
		while($row = $ilDB->fetchAssoc($res))
		{
			$a_correct[$row['user_id'].':'.$row['cp_node_id']] = $row['counter'];
		}

		$res = $ilDB->queryF($query,array('text'),array('incorrect'));
		while($row = $ilDB->fetchAssoc($res))
		{
			$a_incorrect[$row['user_id'].':'.$row['cp_node_id']] = $row['counter'];
		}

		$query = 'SELECT cmi_node.user_id, cmi_node.cp_node_id, count(*) as counter
				FROM cmi_interaction, cmi_node 
				WHERE '.$ilDB->in('cp_node_id', $a_sco, false, 'integer') .' 
				AND '.$ilDB->in('cmi_node.user_id', $a_user, false, 'integer') .'
				AND cmi_node.cmi_node_id = cmi_interaction.cmi_node_id 
				AND cmi_interaction.result <> %s AND  cmi_interaction.result <> %s
				GROUP BY cmi_node.user_id,cmi_node.cp_node_id';
		$res = $ilDB->queryF($query,array('text','text'),array('correct','incorrect'));
		while($row = $ilDB->fetchAssoc($res))
		{
			$a_other[$row['user_id'].':'.$row['cp_node_id']] = $row['counter'];
		}

		$dbdata = array();
		$query = 'SELECT user_id, cp_node_id, '
			. 'audio_captioning, audio_level, completion_status, completion_threshold, credit, delivery_speed, '
			. 'c_entry, c_exit, c_language, location as c_location, c_mode, progress_measure, c_max, c_min, c_raw, scaled, '
			. 'scaled_passing_score, session_time, success_status, total_time, c_timestamp, suspend_data, launch_data '
			. 'FROM cmi_node '
			. 'WHERE '.$ilDB->in('cp_node_id', $a_sco, false, 'integer') .' '
			. 'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
			. 'ORDER BY ';
			if ($b_orderBySCO) $query.='cp_node_id, user_id';
			else $query.='user_id, cp_node_id';
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$dbdata[] = $row;
		}
		foreach($dbdata as $data) {
			$skey = $data["user_id"].':'.$data["cp_node_id"];
			$correct = 0;
			if($a_correct[$skey] != null) $correct = $a_correct[$skey];
			$incorrect = 0;
			if($a_incorrect[$skey] != null) $incorrect = $a_incorrect[$skey];
			$other = 0;
			if($a_other[$skey] != null) $other = $a_other[$skey];
			$all = $correct+$incorrect+$other;
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
			$data["sco_marked_for_learning_progress"] = $scoProgress[$data["cp_node_id"]];
			$data["sco_title"] = $scoTitles[$data["cp_node_id"]];
			$data["counter_i_correct"] = $correct;
			if ($all > 0) $data["counter_i_correct_percent"] = $correct*100/$all;
			else $data["counter_i_correct_percent"] = 0;
			$data["counter_i_incorrect"] = $incorrect;
			if ($all > 0) $data["counter_i_incorrect_percent"] = $incorrect*100/$all;
			else $data["counter_i_incorrect_percent"] = 0;
			$data["counter_i_other"] = $other;
			if ($all > 0) $data["counter_i_other_percent"] = $other*100/$all;
			else $data["counter_i_other_percent"] = 0;
			$data["audio_captioning"] = "".$data["audio_captioning"];
			$data["audio_level"] = "".$data["audio_level"];
			$data["completion_status"] = "".$data["completion_status"];
			$data["completion_threshold"] = "".$data["completion_threshold"];
			$data["credit"] = "".$data["credit"];
			$data["delivery_speed"] = "".$data["delivery_speed"];
			$data["c_entry"] = "".$data["c_entry"];
			$data["c_exit"] = "".$data["c_exit"];
			$data["c_language"] = "".$data["c_language"];
			$data["c_location"] = "".str_replace('"','',$data["c_location"]);
			$data["c_mode"] = "".$data["c_mode"];
			$data["progress_measure"] = "".$data["progress_measure"];
			$data["c_max"] = "".$data["c_max"];
			$data["c_min"] = "".$data["c_min"];
			$data["c_raw"] = "".$data["c_raw"];
			$data["scaled"] = "".$data["scaled"];//$data["scaled"]*100)
			$data["scaled_passing_score"] = "".$data["scaled_passing_score"];
			$data["session_time"] = "".$data["session_time"];
			$data["session_time_seconds"] = "";
			if ($data["session_time"] != "") $data["session_time_seconds"] = round(ilObjSCORM2004LearningModule::_ISODurationToCentisec($data["session_time"])/100);
			$data["success_status"] = "".$data["success_status"];
			$data["total_time"] = "".$data["total_time"];
			$data["total_time_seconds"] = "";
			if ($data["total_time"] != "") $data["total_time_seconds"] = round(ilObjSCORM2004LearningModule::_ISODurationToCentisec($data["total_time"])/100);
			$data["c_timestamp"] = $data["c_timestamp"];//ilDatePresentation::formatDate(new ilDateTime($data["c_timestamp"],IL_CAL_UNIX));
			$data["suspend_data"] = "".$data["suspend_data"];
			$data["launch_data"] = "".$data["launch_data"];
				// if ($data["success_status"]!="" && $data["success_status"]!="unknown") {
					// $status = $data["success_status"];
				// } else {
					// if ($data["completion_status"]=="") {
						// $status="unknown";
					// } else {
						// $status = $data["completion_status"];
					// }
				// }
			$returnData[]=$data;
		}
		return $returnData;
	}


	function tracInteractionUserAnswersColumns($a_user = array(), $a_sco = array(),$b_orderBySCO, $b_allowExportPrivacy) {
		global $lng, $ilDB;
		$lng->loadLanguageModule("scormtrac");
		// default fields
		$cols = array();
		$a_interaction=array();
		$a_interactionDescription=array();
		$dbdata = array();
		$query = 'SELECT cmi_node.cp_node_id,
				cmi_interaction.cmi_interaction_id, 
				cmi_interaction.id, 
				cmi_interaction.description 
				FROM cmi_interaction, cmi_node 
				WHERE '.$ilDB->in('cp_node_id', $a_sco, false, 'integer') .' 
				AND '.$ilDB->in('cmi_node.user_id', $a_user, false, 'integer') .'
				AND cmi_node.cmi_node_id = cmi_interaction.cmi_node_id
				ORDER BY ';
			if ($b_orderBySCO) $query.='cmi_node.cp_node_id, cmi_node.user_id';
			else $query.='cmi_node.user_id, cmi_node.cp_node_id';
			$query.=', cmi_interaction.cmi_interaction_id, cmi_interaction.id';

		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$dbdata[] = $row;
		}
		foreach($dbdata as $data) {
			$key = $data["cp_node_id"].':'.$data["id"];
			$exist=false;
			for ($i=0;$i<count($a_interaction);$i++) {
				if ($a_interaction[$i] == $key) $exist=true;
			}
			if ($exist==false) $a_interaction[] = $key;
			if ($a_interactionDescription[$key]==null) $a_interactionDescription[$key] = "".$data["description"];
		}
		$a_cols=explode(',',
			'lm_id,lm_title,cp_node_id,sco_marked_for_learning_progress,sco_title,user_id,login,name,email,department');
		$a_true=explode(',','name,sco_title');
		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		for ($i=0;$i<count($a_interaction);$i++) {
//			$cols["interaction_id".$i] = array("txt" => $lng->txt("interaction_id").' '.$i,"default" => false);
//			if ($a_interactionDescription[$a_interaction[$i]] != "") {
//				$cols["interaction_description".$i] = array("txt" => $lng->txt("interaction_description").' '.$i,"default" => false);
//			}
//			$cols["interaction_value".$i] = array("txt" => $lng->txt("interaction_value").' '.$i,"default" => true);//$a_interactionDescription[$a_interaction[$i]]
			$cols["interaction_value".$i." ".$a_interactionDescription[$a_interaction[$i]]] = array("txt" => sprintf($lng->txt("interaction_value"),$i)." ".$a_interactionDescription[$a_interaction[$i]],"default" => true);
		}
		return $cols;
	}

	function tracInteractionUserAnswers($a_user = array(), $a_sco = array(), $b_orderBySCO=false, $allowExportPrivacy=false) {
		global $ilDB, $lng;
		$lng->loadLanguageModule("scormtrac");

		$returnData = array();

		$scoTitles = self::scoTitlesForExportSelected();

		$scoProgress = self::markedLearningStatusForExportSelected($scoTitles);

		$a_interaction=array();
		$a_interactionId=array();
		$a_interactionDescription=array();
		$a_interactionUser=array();
		if ($allowExportPrivacy == true) $userArray = self::userArrayForExportSelected($a_user);
		$dbdata = array();
		$query = 'SELECT cmi_node.user_id, cmi_node.cp_node_id,
				cmi_interaction.cmi_interaction_id, 
				cmi_interaction.id, 
				cmi_interaction.result, 
				cmi_interaction.description 
				FROM cmi_interaction, cmi_node 
				WHERE '.$ilDB->in('cp_node_id', $a_sco, false, 'integer') .' 
				AND '.$ilDB->in('cmi_node.user_id', $a_user, false, 'integer') .'
				AND cmi_node.cmi_node_id = cmi_interaction.cmi_node_id
				ORDER BY ';
			if ($b_orderBySCO) $query.='cmi_node.cp_node_id, cmi_node.user_id';
			else $query.='cmi_node.user_id, cmi_node.cp_node_id';
			$query.=', cmi_interaction.cmi_interaction_id, cmi_interaction.id';
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$dbdata[] = $row;
		}
		foreach($dbdata as $data) {
			$key = $data["cp_node_id"].':'.$data["id"];
			$exist=false;
			for ($i=0;$i<count($a_interaction);$i++) {
				if ($a_interaction[$i] == $key) $exist=true;
			}
			// if ($exist==false) $a_interaction[] = $key;
			// if ($a_interactionId[$key]==null) $a_interactionId[$key] = "".$data["id"];
			// if ($a_interactionDescription[$key]==null) $a_interactionDescription[$key] = "".$data["description"];
			if ($exist==false) {
				$a_interaction[] = $key;
				$a_interactionId[$key] = "".$data["id"];
				$a_interactionDescription[$key] = "".$data["description"];
			}
			$key .= ':'.$data["user_id"];
			$a_interactionUser[$key] = "".$data["result"];
		}
//		var_dump($a_interactionUser);

		$dbdata = array();
		$query = 'SELECT user_id, cp_node_id '
			. 'FROM cmi_node '
			. 'WHERE '.$ilDB->in('cp_node_id', $a_sco, false, 'integer') .' '
			. 'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
			. 'GROUP BY user_id '
			. 'ORDER BY ';
			if ($b_orderBySCO) $query.='cp_node_id, user_id';
			else $query.='user_id, cp_node_id';
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$dbdata[] = $row;
		}
		foreach($dbdata as $data) {
			for ($i=0;$i<count($a_interaction);$i++) {
				// $data["interaction_id".$i] = $a_interactionId[$a_interaction[$i]];
				// $data["interaction_description".$i] = $a_interactionDescription[$a_interaction[$i]];
				// $data["interaction_value".$i] = "";
				// $ukey=$a_interaction[$i].':'.$data["user_id"];
				// if ($a_interactionUser[$ukey] != null) $data["interaction_value".$i] = $a_interactionUser[$ukey];
				$intdesc = "interaction_value".$i." ".$a_interactionDescription[$a_interaction[$i]];
				$data[$intdesc] = "";
				$ukey=$a_interaction[$i].':'.$data["user_id"];
				if ($a_interactionUser[$ukey] != null) $data[$intdesc] = $a_interactionUser[$ukey];
			}
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
			$data["sco_marked_for_learning_progress"] = $scoProgress[$data["cp_node_id"]];
			$data["sco_title"] = $scoTitles[$data["cp_node_id"]];
			$returnData[]=$data;
		}
//		var_dump($returnData);
		return $returnData;
	}

}