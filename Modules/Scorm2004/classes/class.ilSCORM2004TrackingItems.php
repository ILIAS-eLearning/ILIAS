<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/ScormAicc/classes/class.ilSCORMTrackingItems.php';
include_once './Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php';
/**
* Class ilSCORM2004TrackingItems
*
* @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
*
* @ingroup ModulesScorm2004
*/
class ilSCORM2004TrackingItems extends ilSCORMTrackingItems
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

	function exportSelectedCoreColumns($b_orderBySCO, $b_allowExportPrivacy) {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		// default fields
		$cols = array();
		$udh=self::userDataHeaderForExport();
		$a_cols=explode(',',
			'lm_id,lm_title,cp_node_id,sco_marked_for_learning_progress,sco_title,'.$udh["cols"]
			.',audio_captioning,audio_level,completion_status,completion_threshold,credit,delivery_speed'
			.',c_entry,c_exit,c_language,c_location,c_mode,progress_measure,c_max,c_min,c_raw,scaled'
			.',scaled_passing_score,session_time,session_time_seconds,success_status,total_time,total_time_seconds,c_timestamp,suspend_data,launch_data');
		$a_true=explode(',',$udh["default"].",sco_title,success_status,completion_status");
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
			$data=array_merge($data,self::userDataArrayForExport($data["user_id"], $allowExportPrivacy));
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
	
	function exportSelectedInteractionsColumns() {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		$cols = array();
		$udh=self::userDataHeaderForExport();
		$a_cols=explode(',',
			'lm_id,lm_title,cp_node_id,sco_marked_for_learning_progress,sco_title,'.$udh["cols"]
			.',id,description,weighting,c_type,result,latency,latency_seconds,c_timestamp,learner_response');
		$a_true=explode(',',$udh["default"].",sco_title,id,result,learner_response");//note for trunk: id instead of description
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
			$data=array_merge($data,self::userDataArrayForExport($data["user_id"], $allowExportPrivacy));
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

	function exportSelectedObjectivesColumns() {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		$cols = array();
		$udh=self::userDataHeaderForExport();
		$a_cols=explode(',',
			'lm_id,lm_title,cp_node_id,sco_marked_for_learning_progress,sco_title,'.$udh["cols"]
			.',id,description,completion_status,progress_measure,success_status,scaled,c_max,c_min,c_raw,scope');
		$a_true=explode(',',$udh["default"].",sco_title,id,completion_status,success_status");
		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		return $cols;
	}

	function exportSelectedObjectives($a_user = array(), $a_sco = array(), $b_orderBySCO=false, $allowExportPrivacy=false) {
		global $ilDB, $lng;
		$lng->loadLanguageModule("scormtrac");

		$returnData = array();

		$scoTitles = self::scoTitlesForExportSelected();

		$scoProgress = self::markedLearningStatusForExportSelected($scoTitles);

		$dbdata = array();
		$query = 'SELECT cmi_node.user_id, cmi_node.cp_node_id,
				cmi_objective.cmi_objective_id,
				cmi_objective.id, 
				cmi_objective.description, 
				cmi_objective.completion_status,
				cmi_objective.progress_measure,
				cmi_objective.success_status,
				cmi_objective.scaled,
				cmi_objective.c_max,
				cmi_objective.c_min,
				cmi_objective.c_raw,
				cmi_objective.scope 
				FROM cmi_objective, cmi_node 
				WHERE '.$ilDB->in('cp_node_id', $a_sco, false, 'integer') .' 
				AND '.$ilDB->in('cmi_node.user_id', $a_user, false, 'integer') .'
				AND cmi_node.cmi_node_id = cmi_objective.cmi_node_id 
				AND cmi_interaction_id is null 
				ORDER BY ';
			if ($b_orderBySCO) $query.='cmi_node.cp_node_id, cmi_node.user_id';
			else $query.='cmi_node.user_id, cmi_node.cp_node_id';
			$query.=', cmi_objective.cmi_node_id';
		$res = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($res))
		{
			$dbdata[] = $row;
		}
		foreach($dbdata as $data) {
			$data["lm_id"] = $this->getObjId();
			$data["lm_title"] = $this->lmTitle;
			$data=array_merge($data,self::userDataArrayForExport($data["user_id"], $allowExportPrivacy));
			$data["sco_marked_for_learning_progress"] = $scoProgress[$data["cp_node_id"]];
			$data["sco_title"] = $scoTitles[$data["cp_node_id"]];
			$data["description"] = "".$data["description"]; 
			$data["completion_status"] = "".$data["completion_status"];
			$data["progress_measure"] = "".$data["progress_measure"];
			$data["success_status"] = "".$data["success_status"];
			$data["scaled"] = "".$data["scaled"];
			$data["c_max"] = "".$data["c_max"];
			$data["c_min"] = "".$data["c_min"];
			$data["c_raw"] = "".$data["c_raw"];
			$data["scope"] = "".$data["scope"];
			$returnData[]=$data;
		}
//		var_dump($returnData);
		return $returnData;
	}

	function exportObjGlobalToSystemColumns() {
		global $lng;
		$lng->loadLanguageModule("scormtrac");
		$cols = array();
		$udh=self::userDataHeaderForExport();
		$a_cols=explode(',',
			'lm_id,lm_title,'.$udh["cols"]
			.',Status,satisfied,measure,c_raw,c_min,c_max,completion_status,progress_measure');
		$a_true=explode(',',$udh["default"].",lm_title,Status,satisfied,completion_status");
		for ($i=0;$i<count($a_cols);$i++) {
			$cols[$a_cols[$i]] = array("txt" => $lng->txt($a_cols[$i]),"default" => false);
		}
		for ($i=0;$i<count($a_true);$i++) {
			$cols[$a_true[$i]]["default"] = true;
		}
		return $cols;
	}

	function exportObjGlobalToSystem($a_user = array(), $allowExportPrivacy=false) {
		global $ilDB, $lng;
		$lng->loadLanguageModule("scormtrac");
		$returnData = array();
		$dbdata = array();
		$query = 'SELECT user_id, scope_id,
				status,
				satisfied,
				measure,
				score_raw as c_raw,
				score_min as c_min,
				score_max as c_max,
				completion_status,
				progress_measure
				FROM cmi_gobjective 
				WHERE scope_id = %s  
				AND '.$ilDB->in('user_id', $a_user, false, 'integer') .'
				ORDER BY user_id, scope_id';
		$res = $ilDB->queryF($query,array('integer'),array($this->getObjId()));
		while($row = $ilDB->fetchAssoc($res))
		{
			$dbdata[] = $row;
		}
		foreach($dbdata as $data) {
			$data["lm_id"] = $data["scope_id"];
			$data["lm_title"] = $this->lmTitle;
			$data=array_merge($data,self::userDataArrayForExport($data["user_id"], $allowExportPrivacy));
			$data["Status"] = "".$data["status"];
			$data["satisfied"] = "".$data["satisfied"];
			$data["measure"] = "".$data["measure"];
			$data["c_raw"] = "".$data["c_raw"];
			$data["c_min"] = "".$data["c_min"];
			$data["c_max"] = "".$data["c_max"];
			$data["completion_status"] = "".$data["completion_status"];
			$data["progress_measure"] = "".$data["progress_measure"];
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
		$udh=self::userDataHeaderForExport();
		$a_cols=explode(',',
			'lm_id,lm_title,cp_node_id,sco_marked_for_learning_progress,sco_title,'.$udh["cols"]
			.',counter_i_correct,counter_i_correct_percent'
			.',counter_i_incorrect,counter_i_incorrect_percent'
			.',counter_i_other,counter_i_other_percent'
			.',audio_captioning,audio_level,completion_status,completion_threshold,credit,delivery_speed'
			.',c_entry,c_exit,c_language,c_location,c_mode,progress_measure,c_max,c_min,c_raw,scaled'
			.',scaled_passing_score,session_time,session_time_seconds,success_status,total_time,total_time_seconds,c_timestamp,suspend_data,launch_data');
		$a_true=explode(',',$udh["default"].',sco_title'
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
			$data=array_merge($data,self::userDataArrayForExport($data["user_id"], $allowExportPrivacy));
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
		$udh=self::userDataHeaderForExport();
		$a_cols=explode(',','lm_id,lm_title,cp_node_id,sco_marked_for_learning_progress,sco_title,'.$udh["cols"]);
		$a_true=explode(',',$udh["default"].",sco_title");
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
			$data=array_merge($data,self::userDataArrayForExport($data["user_id"], $allowExportPrivacy));
			$data["sco_marked_for_learning_progress"] = $scoProgress[$data["cp_node_id"]];
			$data["sco_title"] = $scoTitles[$data["cp_node_id"]];
			$returnData[]=$data;
		}
//		var_dump($returnData);
		return $returnData;
	}
	

	function exportSelectedSuccess($a_user = array(), $allowExportPrivacy=false) {
		global $ilDB;

		$scoCounter = 0;
		$query = 'SELECT count(distinct(cp_node.cp_node_id)) counter '
			. 'FROM cp_node, cp_resource, cp_item '
			. 'WHERE cp_item.cp_node_id = cp_node.cp_node_id ' 
			. 'AND cp_item.resourceid = cp_resource.id AND scormtype = %s ' 
			. 'AND nodename = %s AND cp_node.slm_id = %s';
	 	$res = $ilDB->queryF(
			$query,
		 	array('text', 'text', 'integer'),
		 	array('sco', 'item', $this->getObjId())
		);		
		while($row = $ilDB->fetchAssoc($res))
		{
			$scoCounter = $row['counter'];
		}

		$u_startedSCO = array();
		$u_completedSCO = array();
		$u_passedSCO = array();
		for($i=0; $i<count($a_user); $i++) {
			$u_startedSCO[$a_user[$i]] = 0;
			$u_completedSCO[$a_user[$i]] = 0;
			$u_passedSCO[$a_user[$i]] = 0;
		}

		$query = 'SELECT user_id, count(*) counter '
			. 'FROM cmi_node, cp_node ' 
			. 'WHERE cmi_node.cp_node_id = cp_node.cp_node_id ' 
			. 'AND cp_node.slm_id = %s '
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
			. 'FROM cmi_node, cp_node ' 
			. 'WHERE cmi_node.cp_node_id = cp_node.cp_node_id ' 
			. 'AND cp_node.slm_id = %s '
			. "AND cmi_node.completion_status = 'completed' "
			. 'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
			. 'GROUP BY user_id';
		$res = $ilDB->queryF(
			$query,
			array('integer'),
			array($this->getObjId())
		);
		while ($data = $ilDB->fetchAssoc($res)) {
			$u_completedSCO[$data['user_id']] = $data['counter'];
		}

		$query = 'SELECT user_id, count(*) counter '
			. 'FROM cmi_node, cp_node ' 
			. 'WHERE cmi_node.cp_node_id = cp_node.cp_node_id ' 
			. 'AND cp_node.slm_id = %s '
			. "AND cmi_node.success_status = 'passed' "
			. 'AND '.$ilDB->in('user_id', $a_user, false, 'integer') .' '
			. 'GROUP BY user_id';
		$res = $ilDB->queryF(
			$query,
			array('integer'),
			array($this->getObjId())
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
		//CertificateDate?
	}
	
		// function exportSelectedObjectives($a_user = array()) {
		// global $ilDB;

		// $csv = null;

		// include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		// $privacy = ilPrivacySettings::_getInstance();
		// $allowExportPrivacy = $privacy->enabledExportSCORM();

		// $scoTitles = $this->scoTitlesForExportSelected();

		// $scoProgress = $this->markedLearningStatusForExportSelected($scoTitles);

		// if ($allowExportPrivacy == true) $userString = $this->userStringForExportSelected($a_user);

		// $dbdata = array();
		// $query = 'SELECT cmi_node.user_id, cmi_node.cp_node_id,
				// cmi_objective.id, 
				// cmi_objective.description, 
				// cmi_objective.completion_status, 
				// cmi_objective.progress_measure, 
				// cmi_objective.success_status, 
				// cmi_objective.c_max, 
				// cmi_objective.c_min, 
				// cmi_objective.c_raw, 
				// cmi_objective.scaled, 
				// cmi_objective.scope, 
				// cmi_objective.cmi_objective_id 
				// FROM cmi_objective, cmi_node 
				// INNER JOIN cp_node ON cp_node.cp_node_id = cmi_node.cp_node_id
				// WHERE '.$ilDB->in('cmi_node.user_id', $a_user, false, 'integer') .' 
				// AND cmi_objective.cmi_interaction_id is null
				// AND cp_node.slm_id = %s 
				// AND cmi_node.cmi_node_id = cmi_objective.cmi_node_id
				// ORDER BY cmi_node.user_id, cmi_node.cp_node_id, cmi_objective.id, cmi_objective.cmi_objective_id';
		// $res = $ilDB->queryF(
			// $query,
			// array('integer'),
			// array($this->getId())
		// );
		// while($row = $ilDB->fetchAssoc($res))
		// {
			// $dbdata[] = $row;
		// }
		
		// foreach($dbdata as $data) {
			// $csv = $csv. $this->getId()
				// . ";\"" . $this->title ."\""
				// . ";" . $data["user_id"];
			// if ($allowExportPrivacy == true) $csv .= $userString[$data["user_id"]];
			// $csv .= ';' . $data["cp_node_id"]
				// . ';"' .$scoProgress[$data["cp_node_id"]] .'"'
				// . ';"' . $scoTitles[$data["cp_node_id"]] .'"'
				// . ';"' . $data["cp_node_id"].'-'.$data["id"] .'"'
				// . ';"' . $data["id"] .'"'
				// . ';"' . str_replace('"','',$data["description"]) .'"'
				// . ';"' . $data["completion_status"] .'"'
				// . ';' . $data["progress_measure"]
				// . ';"' . $data["success_status"] .'"'
				// . ';' . $data["c_max"]
				// . ';' . $data["c_min"]
				// . ';' . $data["c_raw"]
				// . ';' . $data["scaled"]
				// . ';"' . $data["scope"] .'"'
// //				. ';' . $data["cmi_objective_id"]
// //				. ';' . $data["cmi_node_id"]
// //				. ';' . $data["cmi_interaction_id"]
				// . "\n";
		// }

		// $header = "LearningModuleId;LearningModuleTitle;UserId;";
		// if ($allowExportPrivacy == true) $header .= "Login;Name;Email;Department;";
		// $header .= "SCOId;SCOmarkedForLearningProgress;SCOTitle;combinedID;"
				// . "id;description;completion_status;progress_measure;success_status;"
				// . "score.max;score.min;score.raw;score.scaled;scope\n";//cmi_objective_id;cmi_node_id;

		// $this->sendExportFile($header, $csv, "SCOobjectives");
	// }

	
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

}