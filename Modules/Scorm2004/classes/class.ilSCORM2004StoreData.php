<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilSCORM2004StoreData
*
* @author Alex Killing <alex.killing@gmx.de>, Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
*
* @ingroup ModulesScormAicc
*/
class ilSCORM2004StoreData
{

	public function scormPlayerUnload($userId=null, $packageId)
	{
		global $ilDB;
				
		$data = json_decode(is_string($data) ? $data : file_get_contents('php://input'));
		if (!$data) return;
		if ($userId == null) {
			$userId=(int) $data->p;
			self::checkIfAllowed($packageId,$userId,$data->hash); 
		}
		if ($data->last !="") $this->set_last_visited($packageId,$userId,$data->last);

		$endDate = date('Y-m-d H:i:s', mktime(date('H'), date('i')+5, date('s'), date('m'), date('d'), date('Y')));
		$ilDB->manipulateF('
			UPDATE cmi_custom 
			SET c_timestamp = %s
			WHERE user_id = %s 
			AND	sco_id = %s 
			AND obj_id = %s 
			AND	lvalue = %s',  
			array('timestamp', 'integer', 'integer', 'integer', 'text'),
			array($endDate, $userId, 0, $packageId, 'hash')
		);
		
		//TODO UK increase performance for _syncReadEvent e.g. compute time on client or reduce sql-calls
		// sync access number and time in read event table
		include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php");
		ilSCORM2004Tracking::_syncReadEvent($packageId, $userId, "sahs", (int)$_GET['ref_id']);

		header('Content-Type: text/plain; charset=UTF-8');
		print("");
	}

	function set_last_visited($a_obj_id, $a_user_id, $last_visited)
	{
		global $ilDB;

		$val_set = $ilDB->queryF('
		SELECT rvalue FROM cmi_custom 
		WHERE user_id = %s
				AND sco_id = %s
				AND lvalue = %s
				AND obj_id = %s',
		array('integer','integer', 'text','integer'),
		array($a_user_id, 0,'last_visited',$a_obj_id));
		
		$val_rec = $ilDB->fetchAssoc($val_set);
		$pre_last_visited=$val_rec["rvalue"];

		if ($pre_last_visited == $last_visited) return;
		if ($pre_last_visited == null) {
			$ilDB->manipulateF('
				INSERT INTO cmi_custom (rvalue, user_id, sco_id, obj_id, lvalue, c_timestamp)
				VALUES(%s, %s, %s, %s, %s, %s)',  
				array('text', 'integer', 'integer', 'integer', 'text', 'timestamp'),
				array($last_visited, $a_user_id, 0, $a_obj_id, 'last_visited', date('Y-m-d H:i:s'))
			);
		}
		else
		{
			$ilDB->manipulateF('
				UPDATE cmi_custom 
				SET rvalue = %s, 
					c_timestamp = %s
				WHERE user_id = %s 
				AND	sco_id = %s 
				AND obj_id = %s 
				AND	lvalue = %s',  
				array('text', 'timestamp', 'integer', 'integer', 'integer', 'text'),
				array($last_visited, date('Y-m-d H:i:s'), $a_user_id, 0, $a_obj_id, 'last_visited')
			);
		}
	}


	public function persistCMIData($userId=null, $packageId, $defaultLessonMode, $comments, $interactions, $objectives, $data = null)
	{
		global $ilLog;

		if ($defaultLessonMode == "browse") {return;}

		$jsMode = strpos($_SERVER['HTTP_ACCEPT'], 'text/javascript')!==false;

		$data = json_decode(is_string($data) ? $data : file_get_contents('php://input'));
		$ilLog->write("dataTo_setCMIData: ".file_get_contents('php://input'));
		if (!$data) return;
		if ($userId == null) {
			$userId=(int) $data->p;
			self::checkIfAllowed($packageId,$userId,$data->hash); 
		}
		$return = ilSCORM2004StoreData::setCMIData(
			$userId, 
			$packageId, 
			$data, 
			$comments,
			$interactions,
			$objectives
			);
		$ilLog->write("SCORM: return of persistCMIData: ".json_encode($return));
		if ($jsMode) 
		{
			header('Content-Type: text/javascript; charset=UTF-8');
			print(json_encode($return));
		}
		else
		{
			header('Content-Type: text/html; charset=UTF-8');
			print(var_export($return, true));
		}
	}

	function checkIfAllowed($packageId,$userId,$hash){
		global $ilDB;
		$res = $ilDB->queryF(
			'select rvalue,c_timestamp from cmi_custom where user_id=%s and obj_id=%s and lvalue=%s and c_timestamp>%s',
			array('integer','integer','text','timestamp'),
			array($userId,$packageId,'hash',date('Y-m-d H:i:s'))
		);
		$rowtmp=$ilDB->fetchAssoc($res);
		if ($rowtmp['rvalue']==$hash) return;
		else die("not allowed");
	}

	private function setCMIData($userId, $packageId, $data,$getComments,$getInteractions,$getObjectives)
	{
		global $ilDB, $ilLog;

		$result = array();

		if (!$data) return;

		$i_check=$data->i_check;
		$i_set=$data->i_set;
		$b_node_update=false;
		$cmi_node_id=null;
		$a_map_cmi_interaction_id=array();

		$tables = array('node', 'comment', 'interaction', 'objective', 'correct_response');
		
		foreach($tables as $table)
		{
			if (!is_array($data->$table)) continue;

			$ilLog->write("SCORM: setCMIData, table -".$table."-");

			// now iterate through data rows from input
			foreach($data->$table as &$row)
			{
				$ilLog->write("Checking table: ".$table);

				switch($table)
				{
					case 'node': //is always first and has only 1 row

						$res = $ilDB->queryF(
							'SELECT cmi_node_id FROM cmi_node WHERE cp_node_id = %s and user_id = %s',
							array('integer','integer'),
							array($row[19],$userId)
						);
						$rowtmp=$ilDB->fetchAssoc($res);
						$cmi_node_id=$rowtmp['cmi_node_id'];
						if ($cmi_node_id!=null) $b_node_update=true;
						else {
							$cmi_node_id = $ilDB->nextId('cmi_node');
							$b_node_update=false;
						}
						$ilLog->write("setCMIdata with cmi_node_id = ".$cmi_node_id);
						$a_data=array(
							'accesscount'			=> array('integer', $row[0]),
							'accessduration'		=> array('text', $row[1]),
							'accessed'				=> array('text', $row[2]),
							'activityabsduration'	=> array('text', $row[3]),
							'activityattemptcount'	=> array('integer', $row[4]),
							'activityexpduration'	=> array('text', $row[5]),
							'activityprogstatus'	=> array('integer', $row[6]),
							'attemptabsduration'	=> array('text', $row[7]),
							'attemptcomplamount'	=> array('float', $row[8]),
							'attemptcomplstatus'	=> array('integer', $row[9]),
							'attemptexpduration'	=> array('text', $row[10]),
							'attemptprogstatus'		=> array('integer', $row[11]),
							'audio_captioning'		=> array('integer', $row[12]),
							'audio_level'			=> array('float', $row[13]),
							'availablechildren'		=> array('text', $row[14]),
							'cmi_node_id'			=> array('integer', $cmi_node_id),
							'completion'			=> array('float', $row[16]),
							'completion_status'		=> array('text', $row[17]),
							'completion_threshold'	=> array('text', $row[18]),
							'cp_node_id'			=> array('integer', $row[19]),
							'created'				=> array('text', $row[20]),
							'credit'				=> array('text', $row[21]),
							'delivery_speed'		=> array('float', $row[22]),
							'c_entry'				=> array('text', $row[23]),
							'c_exit'				=> array('text', $row[24]),
							'c_language'			=> array('text', $row[25]),
							'launch_data'			=> array('clob', $row[26]),
							'learner_name'			=> array('text', $row[27]),
							'location'				=> array('text', $row[28]),
							'c_max'					=> array('float', $row[29]),
							'c_min'					=> array('float', $row[30]),
							'c_mode'				=> array('text', $row[31]),
							'modified'				=> array('text', $row[32]),
							'progress_measure'		=> array('float', $row[33]),
							'c_raw'					=> array('float', $row[34]),
							'scaled'				=> array('float', $row[35]),
							'scaled_passing_score'	=> array('float', $row[36]),
							'session_time'			=> array('text', $row[37]),
							'success_status'		=> array('text', $row[38]),
							'suspend_data'			=> array('clob', $row[39]),
							'total_time'			=> array('text', $row[40]),
							'user_id'				=> array('integer', $userId),
							'c_timestamp'			=> array('timestamp', date('Y-m-d H:i:s')),
							'additional_tables'		=> array('integer', $i_check)
						);
						
						if($b_node_update==false) {
							$ilLog->write("Want to insert row: ".count($row) );
							$ilDB->insert('cmi_node', $a_data);
						} else {
							$ilDB->update('cmi_node', $a_data, array('cmi_node_id' => array('integer', $cmi_node_id)));
							$ilLog->write("updated");
						}
						
						if($b_node_update==true) {
							//remove
							if ($i_set>7) {
								$i_set-=8;
								if ($getComments) {
									$q = 'DELETE FROM cmi_comment WHERE cmi_node_id = %s';
									$ilDB->manipulateF($q, array('integer'), array($cmi_node_id));
								}
							}
							if ($i_set>3) {
								$i_set-=4;
								if ($getInteractions) {
									$q = 'DELETE FROM cmi_correct_response 
									WHERE cmi_interaction_id IN (
									SELECT cmi_interaction.cmi_interaction_id FROM cmi_interaction WHERE cmi_interaction.cmi_node_id = %s)';
									$ilDB->manipulateF($q, array('integer'), array($cmi_node_id));
								}
							}
							if ($i_set>1) {
								$i_set-=2;
								if ($getInteractions) {
									$q = 'DELETE FROM cmi_interaction WHERE cmi_node_id = %s';
									$ilDB->manipulateF($q, array('integer'), array($cmi_node_id));
								}
							}
							if ($i_set>0) {
								$i_set=0;
								if ($getObjectives) { 
									$q = 'DELETE FROM cmi_objective WHERE cmi_node_id = %s';
									$ilDB->manipulateF($q, array('integer'), array($cmi_node_id));
								}
							}
							//end remove
						}
						//to send to client
						$result[(string)$row[19]] = $cmi_node_id;
					break;

					case 'comment':
						$row[0] = $ilDB->nextId('cmi_comment');
	
						$ilDB->insert('cmi_comment', array(
							'cmi_comment_id'	=> array('integer', $row[0]),
							'cmi_node_id'		=> array('integer', $cmi_node_id),
							'c_comment'			=> array('clob', $row[2]),
							'c_timestamp'		=> array('text', $row[3]),
							'location'			=> array('text', $row[4]),
							'sourceislms'		=> array('integer', $row[5])
						));
					break;

					case 'interaction':
						$cmi_interaction_id = $ilDB->nextId('cmi_interaction');
						$a_map_cmi_interaction_id[]=array($row[0],$cmi_interaction_id);
						$ilDB->insert('cmi_interaction', array(
							'cmi_interaction_id'	=> array('integer', $cmi_interaction_id),
							'cmi_node_id'			=> array('integer', $cmi_node_id),
							'description'			=> array('clob', $row[2]),
							'id'					=> array('text', $row[3]),
							'latency'				=> array('text', $row[4]),
							'learner_response'		=> array('clob', $row[5]),
							'result'				=> array('text', $row[6]),
							'c_timestamp'			=> array('text', $row[7]),
							'c_type'				=> array('text', $row[8]),
							'weighting'				=> array('float', $row[9])
						));
					break;

					case 'objective':
						$row[2] = $ilDB->nextId('cmi_objective');
						$cmi_interaction_id = null;
						if ($row[0] != null) {
							for($i=0;$i<count($a_map_cmi_interaction_id);$i++) 
								if ($row[0] == $a_map_cmi_interaction_id[$i][0]) $cmi_interaction_id=$a_map_cmi_interaction_id[$i][1];
						}
						$ilDB->insert('cmi_objective', array(
							'cmi_interaction_id'	=> array('integer', $cmi_interaction_id),
							'cmi_node_id'			=> array('integer', $cmi_node_id),
							'cmi_objective_id'		=> array('integer', $row[2]),
							'completion_status'		=> array('text', $row[3]),
							'description'			=> array('clob', $row[4]),
							'id'					=> array('text', $row[5]),
							'c_max'					=> array('float', $row[6]),
							'c_min'					=> array('float', $row[7]),
							'c_raw'					=> array('float', $row[8]),
							'scaled'				=> array('float', $row[9]),
							'progress_measure'		=> array('float', $row[10]),
							'success_status'		=> array('text', $row[11]),
							'scope'					=> array('text', $row[12])
						));
					break;

					case 'correct_response':
						$cmi_interaction_id = null;
						if ($row[1] !== null) {
							for($i=0;$i<count($a_map_cmi_interaction_id);$i++) 
								if ($row[1] == $a_map_cmi_interaction_id[$i][0]) $cmi_interaction_id=$a_map_cmi_interaction_id[$i][1];
							$row[0] = $ilDB->nextId('cmi_correct_response');
							$ilDB->insert('cmi_correct_response', array(
								'cmi_correct_resp_id'	=> array('integer', $row[0]),
								'cmi_interaction_id'	=> array('integer', $cmi_interaction_id),
								'pattern'				=> array('text', $row[2])
							));
						}
					break;
				}
			}
		}

		$changed_seq_utilities=$data->changed_seq_utilities;
		$ilLog->write("SCORM2004 adl_seq_utilities changed: ".$changed_seq_utilities);
//		if ($changed_seq_utilities == 1) {
			$returnAr=ilSCORM2004StoreData::writeGObjective($data->adl_seq_utilities, $userId, $packageId);
//		}
		$completed=$returnAr[0];
		$satisfied=$returnAr[1];
		$measure=$returnAr[2];

		$lp_mode=$data->lp_mode;
		if ($lp_mode=="12") //12=scorm_package - has to be checked UK
		{
//			include_once './Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php';
//			$new_global_status = ilSCORM2004Tracking::parseLPStatus($completed, $satisfied);
			$new_global_status="";
		}
		else $new_global_status = $data->now_global_status; //6=selected scos, 0=no tracking
		$ilLog->write("new_global_status=".$new_global_status);
		$saved_global_status=$data->saved_global_status;
		$ilLog->write("saved_global_status=".$saved_global_status);
		$result["new_global_status"]=$new_global_status;
		//ATTENTION not at commit - do at unload!
		// sync access number and time in read event table
		//include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php");
		//ilSCORM2004Tracking::_syncReadEvent($packageId, $userId, "sahs", $a_ref_id);

		if($saved_global_status != $new_global_status)
		{
			// update learning progress
			self::ensureObjectDataCacheExistence();
			global $ilObjDataCache;
//			include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
//			ilLPStatusWrapper::_updateStatus($packageId, $userId);
			include_once("./Services/Tracking/classes/class.ilObjUserTracking.php");
			include_once("./Services/Tracking/classes/class.ilLPStatus.php");
			ilLPStatus::writeStatus($packageId, $userId,$new_global_status,$data->percentageCompleted);

//			include_once './Modules/Scorm2004/classes/class.ilSCORM2004Tracking.php';
//			$new_global_status = ilSCORM2004Tracking::updateGlobalStatus($userId, $packageId,$completed, $satisfied, $measure);

//			here put code for soap to MaxCMS e.g. when if($saved_global_status != $new_global_status)
		}
		return $result;
	}


	//saves global_objectives to database
	//$dowrite only if changed adl_seq_utilities
	public function writeGObjective($g_data, $user, $package)
	{
		global $ilDB, $ilLog;
		$ilLog->write("SCORM2004 writeGObjective");

		$returnAr=array(null,null,null);

		//iterate over assoziative array
		if($g_data == null)
			return $returnAr;
		
		$rows_to_insert = Array();
		
		foreach($g_data as $key => $value)
		{			
			$ilLog->write("SCORM2004 writeGObjective -key: ".$key);
			//objective 
			//learner = ilias learner id
			//scope = null / course
		    foreach($value as $skey => $svalue)
			{
				$ilLog->write("SCORM2004 writeGObjective -skey: ".$skey);
		    	//we always have objective and learner id
		    	if($g_data->$key->$skey->$user->$package)
				{
		    		$o_value = $g_data->$key->$skey->$user->$package;
		    		$scope = $package;
		    	}
				else //UK: is this okay? can $scope=0 and $user->{"null"}; when is $scope used?

				{
		    		//scope 0
		    		$o_value = $g_data->$key->$skey->$user->{"null"};
		    		//has to be converted to NULL in JS Later
		    		$scope = 0;
		    	}
				
		    	//insert into database
		    	$objective_id = $skey;
		    	$toset = $o_value;
		    	$dbuser = $user;

		    	if($key == "status")
				{

					//special handling for status
					$completed = $g_data->$key->$skey->$user->{completed};
					$measure = $g_data->$key->$skey->$user->{measure};
					$satisfied = $g_data->$key->$skey->$user->{satisfied};

					$returnAr=array($completed, $satisfied, $measure);

					$obj = '-course_overall_status-';	
					$pkg_id = $package;
					
		    		$res = $ilDB->queryF('
			    		SELECT user_id FROM cmi_gobjective
			    		WHERE objective_id =%s 
			    		AND user_id = %s
			    		AND scope_id = %s', 
		    			array('text', 'integer', 'integer'), 
		    			array($obj, $dbuser, $pkg_id)
					);
		    		$ilLog->write("SCORM2004 Count is: ".$ilDB->numRows($res));
		    		if(!$ilDB->numRows($res))	
		    		{
		    			$ilDB->manipulateF('
				    		INSERT INTO cmi_gobjective
				    		(user_id, status, scope_id, measure, satisfied, objective_id) 
				    		VALUES (%s, %s, %s, %s, %s, %s)',
				    		array('integer', 'text', 'integer', 'text', 'text', 'text'), 
				    		array($dbuser, $completed, $pkg_id, $measure, $satisfied, $obj)
						);
						$ilLog->write("SCORM2004 cmi_gobjective Insert status=".$completed." scope_id=".$pkg_id." measure=".$measure." satisfied=".$satisfied." objective_id=".$obj);
		    		}
		    		else
		    		{
		    			$ilDB->manipulateF('
				    		UPDATE cmi_gobjective
				    		SET status = %s, 
				    			measure = %s,
				    			satisfied = %s 
		    				WHERE objective_id = %s 
			    			AND user_id = %s
			    			AND scope_id = %s', 
				    		array('text', 'text', 'text', 'text', 'integer', 'integer'), 
				    		array($completed, $measure, $satisfied, $obj, $dbuser, $pkg_id)
						);		    			
						$ilLog->write("SCORM2004 cmi_gobjective Update status=".$completed." scope_id=".$pkg_id." measure=".$measure." satisfied=".$satisfied." objective_id=".$obj);
		    		}
				} else //add it to the rows_to_insert
				{
					//create the row if this is the first time it has been found
			    	if($rows_to_insert[$objective_id] == NULL)
				    {
			    		$rows_to_insert[$objective_id] = Array();
			    	}
					$rows_to_insert[$objective_id][$key] = $toset;
				}
					
		    }
	    }
	
	    //Get the scope for all the global objectives!!!
	    $res = $ilDB->queryF("SELECT global_to_system
	    					  FROM cp_package
	    					  WHERE obj_id = %s",
	    					  array('text'),
	    					  array($package)
		    				);
		    				
		$scope_id = ($ilDB->fetchObject($res)->global_to_system) ? 0 : $package;
		
	    //build up the set to look for in the query
	    $existing_key_template = "";
	    foreach(array_keys($rows_to_insert) as $obj_id)
		{
			$existing_key_template .= "'{$obj_id}',";

		}
		//remove trailing ','
		$existing_key_template = substr($existing_key_template, 0, strlen($existing_key_template) - 1);
		$existing_keys = Array();
		
		if($existing_key_template != "")
		{
			//Get the ones that need to be updated in a single query
			$res = $ilDB->queryF("SELECT objective_id 
								  FROM cmi_gobjective 
								  WHERE user_id = %s
							  	  AND scope_id = %s
							 	  AND objective_id IN ($existing_key_template)",
							 	  array('integer', 'integer'),
							 	  array($this->userId, $scope_id)
							     );
							     
			while($row = $ilDB->fetchAssoc($res))
			{
				$existing_keys[] = $row['objective_id'];	
			}
		}
		
		foreach($rows_to_insert as $obj_id => $vals)
		{
			if(in_array($obj_id, $existing_keys))
			{
			     $ilDB->manipulateF("UPDATE cmi_gobjective
									 SET satisfied=%s,
									 	 measure=%s,
									 	 score_raw=%s,
									     score_min=%s,
										 score_max=%s,
										 completion_status=%s,
										 progress_measure=%s
									 WHERE objective_id = %s
									 AND user_id = %s
									 AND scope_id = %s",
									 
									 array('text','text', 'text', 'text', 'text', 'text',
									 	   'text', 'text', 'integer', 'integer'),
									 	   
									 array($vals['satisfied'], $vals["measure"], $vals["score_raw"], 
									 	   $vals["score_min"], $vals["score_max"], 
									 	   $vals["completion_status"], $vals["progress_measure"],
									 	   $obj_id, $this->userId, $scope_id) 	 
								 );
			} else
			{
				$ilDB->manipulateF("INSERT INTO cmi_gobjective
									(user_id, satisfied, measure, scope_id, status, objective_id,
									 score_raw, score_min, score_max, progress_measure, completion_status)
									VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
									
										
									array('integer', 'text', 'text', 'integer', 'text', 'text',
										  'text', 'text', 'text', 'text', 'text'),
										  
									array($this->userId, $vals['satisfied'], $vals['measure'], 
										  $scope_id, NULL, $obj_id, $vals['score_raw'],
										  $vals['score_min'], $vals['score_max'], 
										  $vals['progress_measure'], $vals['completion_status'])	  
								);
			}
		}
		
		// update learning progress here not necessary because integrated in setCMIdata
		// check _updateStatus for cmi_gobjective
//		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");	
//		ilLPStatusWrapper::_updateStatus($package, $user);
		
		return $returnAr;
	}	

	protected static function ensureObjectDataCacheExistence()
	{
		/**
		 * @var $ilObjDataCache ilObjectDataCache
		 */
		global $ilObjDataCache;

		if($ilObjDataCache instanceof ilObjectDataCache)
		{
			return;
		}

		require_once 'classes/class.ilObjectDataCache.php';
		$ilObjDataCache = new ilObjectDataCache();
		$GLOBALS['ilObjDataCache'] = $ilObjDataCache;
	}



}