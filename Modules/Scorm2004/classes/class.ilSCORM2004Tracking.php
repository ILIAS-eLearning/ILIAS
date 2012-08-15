<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilSCORM2004Tracking
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @ingroup ModulesScormAicc
*/
class ilSCORM2004Tracking
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjSCORM2004Tracking()
	{
	}

	function _getInProgress($scorm_item_id,$a_obj_id)
	{
		
die("Not Implemented: ilSCORM2004Tracking_getInProgress");
/*
		global $ilDB;

		if(is_array($scorm_item_id))
		{
			$where = "WHERE sco_id IN(";
			$where .= implode(",",ilUtil::quoteArray($scorm_item_id));
			$where .= ") ";
			$where .= ("AND obj_id = ".$ilDB->quote($a_obj_id)." ");
			   
		}
		else
		{
			$where = "WHERE sco_id = ".$ilDB->quote($scorm_item_id)." ";
			$where .= ("AND obj_id = ".$ilDB->quote($a_obj_id)." ");
		}
				

		$query = "SELECT user_id,sco_id FROM scorm_tracking ".
			$where.
			"GROUP BY user_id, sco_id";
		

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$in_progress[$row->sco_id][] = $row->user_id;
		}
		return is_array($in_progress) ? $in_progress : array();
*/
	}

	function _getCompleted($scorm_item_id,$a_obj_id)
	{
		global $ilDB;
		
die("Not Implemented: ilSCORM2004Tracking_getCompleted");
/*
		if(is_array($scorm_item_id))
		{
			$where = "WHERE sco_id IN(".implode(",",ilUtil::quoteArray($scorm_item_id)).") ";
		}
		else
		{
			$where = "sco_id = ".$ilDB->quote($scorm_item_id)." ";
		}

		$query = "SELECT DISTINCT(user_id) FROM scorm_tracking ".
			$where.
			"AND obj_id = ".$ilDB->quote($a_obj_id)." ".
			"AND lvalue = 'cmi.core.lesson_status' ".
			"AND ( rvalue = 'completed' ".
			"OR rvalue = 'passed')";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->user_id;
		}
		return $user_ids ? $user_ids : array();
*/
	}

	function _getFailed($scorm_item_id,$a_obj_id)
	{
		global $ilDB;
		
die("Not Implemented: ilSCORM2004Tracking_getFailed");
/*
		if(is_array($scorm_item_id))
		{
			$where = "WHERE sco_id IN('".implode("','",$scorm_item_id)."') ";
		}
		else
		{
			$where = "sco_id = '".$scorm_item_id."' ";
		}

		$query = "SELECT DISTINCT(user_id) FROM scorm_tracking ".
			$where.
			"AND obj_id = '".$a_obj_id."' ".
			"AND lvalue = 'cmi.core.lesson_status' ".
			"AND rvalue = 'failed'";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$user_ids[] = $row->user_id;
		}
		return $user_ids ? $user_ids : array();
*/
	}

	/**
	 * Get progress of selected scos
	 * @param object $a_scorm_item_ids
	 * @param object $a_obj_id
	 * @param bool $a_omit_failed do not include success==failed 
	 * @return 
	 */
	function _getCountCompletedPerUser($a_scorm_item_ids, $a_obj_id, $a_omit_failed = false)
	{
		global $ilDB;
		
		$in = $ilDB->in('cp_node.cp_node_id', $a_scorm_item_ids, false, 'integer');
		
		// #8171: success_status vs. completion status
		$omit_failed = '';
		if($a_omit_failed)
		{
			$omit_failed = ' AND success_status <> '.$ilDB->quote('failed', 'text');
		}
		
		$res = $ilDB->queryF('
			SELECT cmi_node.user_id user_id, COUNT(user_id) completed FROM cp_node, cmi_node 
			WHERE '.$in.$omit_failed.'
			AND cp_node.cp_node_id = cmi_node.cp_node_id
			AND cp_node.slm_id = %s
			AND completion_status = %s 
		 	GROUP BY cmi_node.user_id',
			array('integer', 'text'),
			array($a_obj_id, 'completed')
		);		
		while($row = $ilDB->fetchObject($res))		
		{
			$users[$row->user_id] = $row->completed;
		}

		return $users ? $users : array();
	}

	
	/**
	 * Get overall scorm status
	 * @param object $a_obj_id
	 * @return 
	 */
	function _getProgressInfo($a_obj_id)
	{
		global $ilDB;

		$res = $ilDB->queryF('
			SELECT * FROM cmi_gobjective
			WHERE objective_id = %s
			AND scope_id = %s', 
			array('text', 'integer'), 
			array('-course_overall_status-', $a_obj_id)
		);
		
		$info['completed'] = array();
		$info['failed'] = array();
		$info['in_progress'] = array();

		while($row = $ilDB->fetchAssoc($res))
		{
			if (self::_isCompleted($row["status"], $row["status"]))
			{
				$info['completed'][] = $row["user_id"];
			}
			if (self::_isInProgress($row["status"], $row["status"]))
			{
				$info['in_progress'][] = $row["user_id"];
			}
			if (self::_isFailed($row["status"], $row["status"]))
			{
				$info['failed'][] = $row["user_id"];
			}
		}

		return $info;
	}

	/**
	 * Get overall scorm status
	 * @param object $a_obj_id
	 * @return 
	 */
	function _getProgressInfoOfUser($a_obj_id, $a_user_id)
	{
		global $ilDB, $ilLog;

		$res = $ilDB->queryF('
			SELECT * FROM cmi_gobjective
			WHERE objective_id = %s
			AND scope_id = %s AND user_id = %s', 
			array('text', 'integer', 'integer'), 
			array('-course_overall_status-', $a_obj_id, $a_user_id)
		);
		
		$status = "not_attempted";
		if ($row = $ilDB->fetchAssoc($res))
		{
			if (self::_isInProgress($row["status"], $row["status"]))
			{
				$status = "in_progress";
			}
			if (self::_isCompleted($row["status"], $row["status"]))
			{
				$status = "completed";
			}
			if (self::_isFailed($row["status"], $row["status"]))
			{
				$status = "failed";
			}
		}
		return $status;
	}

	/**
	 * Get all tracked users
	 * @param object $a_obj_id
	 * @return 
	 */
	function _getTrackedUsers($a_obj_id)
	{
		global $ilDB, $ilLog;

		$res = $ilDB->queryF('
			SELECT DISTINCT user_id FROM cmi_gobjective
			WHERE objective_id = %s
			AND scope_id = %s', 
			array('text', 'integer'), 
			array('-course_overall_status-', $a_obj_id)
		);
		
		$users = array();
		while ($row = $ilDB->fetchAssoc($res))
		{
			$users[] = $row["user_id"];
		}
		return $users;
	}
	
	function _getItemProgressInfo($a_scorm_item_ids, $a_obj_id, $a_omit_failed = false)
	{
		global $ilDB;
		
		$in = $ilDB->in('cp_node.cp_node_id', $a_scorm_item_ids, false, 'integer');

		$res = $ilDB->queryF(
			'SELECT cp_node.cp_node_id id, 
					cmi_node.user_id user_id,
					cmi_node.completion_status completion, 
					cmi_node.success_status success
			 FROM cp_node, cmi_node 
			 WHERE '.$in.'
			 AND cp_node.cp_node_id = cmi_node.cp_node_id
			 AND cp_node.slm_id = %s',
			array('integer'),
			array($a_obj_id)
		);
		
		$info['completed'] = array();
		$info['failed'] = array();
		$info['in_progress'] = array();

		while($row = $ilDB->fetchAssoc($res))
		{
			// if any data available, set in progress.
			$info['in_progress'][$row["id"]][] = $row["user_id"];
			if ($row["completion"] == "completed" || $row["success"] == "passed")
			{
				// #8171: success_status vs. completion status
				if(!$a_omit_failed || $row["success"] != "failed")
				{
					$info['completed'][$row["id"]][] = $row["user_id"];
				}
			}
			if ($row["success"] == "failed")
			{
				$info['failed'][$row["id"]][] = $row["user_id"];
			}
		}
		return $info;
	}
	
	public static function _getCollectionStatus($a_scos, $a_obj_id, $a_user_id)
	{
		global $ilDB;

		$status = "not_attempted";

		if (is_array($a_scos))
		{
			$in = $ilDB->in('cp_node.cp_node_id', $a_scos, false, 'integer');

			$res = $ilDB->queryF(
				'SELECT cp_node.cp_node_id id,
						cmi_node.completion_status completion, 
						cmi_node.success_status success
				 FROM cp_node, cmi_node 
				 WHERE '.$in.'
				 AND cp_node.cp_node_id = cmi_node.cp_node_id
				 AND cp_node.slm_id = %s
				AND cmi_node.user_id = %s',
				array('integer', 'integer'),
				array($a_obj_id, $a_user_id)
			);

			$started = false;
			$cntcompleted = 0;
			$failed = false;
			while ($rec = $ilDB->fetchAssoc($res))
			{
				if ($rec["completion"] == "completed" || $rec["success"] == "passed")
				{
					$cntcompleted++;
				}
				if ($rec["success"] == "failed") $failed = true;
				$started = true;
			}
			if ($started == true) $status = "in_progress";
			if ($failed == true) $status = "failed";
			else if ($cntcompleted == count($a_scos)) $status = "completed";
			
			// check max attempts
			if ($status == "in_progress" && self::_hasMaxAttempts($a_obj_id, $a_user_id))
			{
				$status = "failed";					
			}			
		}
		return $status;
	}
	
	public static function _hasMaxAttempts($a_obj_id, $a_user_id)
	{
		global $ilDB;
		
		// see ilSCORM13Player
		$res = $ilDB->queryF(
			'SELECT max_attempt FROM sahs_lm WHERE id = %s', 
			array('integer'),
			array($a_obj_id)
		);
		$row = $ilDB->fetchAssoc($res);
		$max_attempts = $row['max_attempt']; 		

		if ($max_attempts)
		{		
			$res = $ilDB->queryF('
				SELECT rvalue FROM cmi_custom 
				WHERE user_id = %s AND sco_id = %s
				AND lvalue = %s	AND obj_id = %s',
				array('integer', 'integer', 'text', 'integer'),
				array($a_user_id, 0, 'package_attempts', $a_obj_id)
			);
			$row = $ilDB->fetchAssoc($res);		

			$row['rvalue'] = str_replace("\r\n", "\n", $row['rvalue']);
			if($row['rvalue'] == null)
			{
				$row['rvalue'] = 0;
			}
			$act_attempts = $row['rvalue'];
			
			if ($act_attempts >= $max_attempts)
			{
				return true;
			}		
		}
		
		return false;
	}

	public static function _countCompleted($a_scos, $a_obj_id, $a_user_id,
		$a_omit_failed = false)
	{
		global $ilDB;
		
		if (is_array($a_scos))
		{
			$in = $ilDB->in('cp_node.cp_node_id', $a_scos, false, 'integer');

			$res = $ilDB->queryF(
				'SELECT cp_node.cp_node_id id,
						cmi_node.completion_status completion, 
						cmi_node.success_status success
				 FROM cp_node, cmi_node 
				 WHERE '.$in.'
				 AND cp_node.cp_node_id = cmi_node.cp_node_id
				 AND cp_node.slm_id = %s
				AND cmi_node.user_id = %s',
				array('integer', 'integer'),
				array($a_obj_id, $a_user_id)
			);
	
			
			$cnt = 0;
			while ($rec = $ilDB->fetchAssoc($res))
			{
				// #8171: alex, added (!$a_omit_failed || $rec["success"] != "failed")
				// since completed/failed combination should not be included in
				// percentage calculation at ilLPStatusSCOM::determinePercentage
				if (($rec["completion"] == "completed" || $rec["success"] == "passed")
					&& (!$a_omit_failed || $rec["success"] != "failed"))
				{
					$cnt++;
				}
			}

		}
		return $cnt;
	}

	/**
	 * Synch read event table
	 *
	 * @param
	 * @return
	 */
	function _syncReadEvent($a_obj_id, $a_user_id, $a_type, $a_ref_id)
	{
		global $ilDB, $ilLog;

		// get attempts
		$val_set = $ilDB->queryF('
		SELECT rvalue FROM cmi_custom 
		WHERE user_id = %s
				AND sco_id = %s
				AND lvalue = %s
				AND obj_id = %s',
		array('integer','integer', 'text','integer'),
		array($a_user_id, 0,'package_attempts',$a_obj_id));
		
		$val_rec = $ilDB->fetchAssoc($val_set);
		
		$val_rec["rvalue"] = str_replace("\r\n", "\n", $val_rec["rvalue"]);
		if ($val_rec["rvalue"] == null) {
			$val_rec["rvalue"]="";
		}

		$attempts = $val_rec["rvalue"];

		// time
		$scos = array();
		$val_set = $ilDB->queryF(
			'SELECT cp_node_id FROM cp_node 
			WHERE nodename = %s
			AND cp_node.slm_id = %s',
			array('text', 'integer'),
			array('item', $a_obj_id)
		);
		while($val_rec = $ilDB->fetchAssoc($val_set))
		{
			array_push($scos,$val_rec['cp_node_id']);
		}
		$time = 0;
		foreach ($scos as $sco) 
		{
			include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
			$data_set = $ilDB->queryF('
				SELECT total_time
				FROM cmi_node 
				WHERE cp_node_id = %s
				AND user_id = %s',
				array('integer','integer'),
				array($sco, $a_user_id)
			);
			
			while($data_rec = $ilDB->fetchAssoc($data_set))
			{
				// see bug report 7246
//				$sec = ilObjSCORM2004LearningModule::_ISODurationToCentisec($data_rec["session_time"]) / 100;
				$sec = ilObjSCORM2004LearningModule::_ISODurationToCentisec($data_rec["total_time"]) / 100; 
			}
			$time += (int) $sec;
			$sec = 0;
//$ilLog->write("++".$time);
		}

		include_once("./Services/Tracking/classes/class.ilChangeEvent.php");
		ilChangeEvent::_recordReadEvent($a_type, $a_ref_id,
			$a_obj_id, $a_user_id, false, $attempts, $time);
	}


	/**
	 * 
	 */
	static function _isCompleted($a_status, $a_satisfied)
	{
		if ($a_status == "completed")
		{
			return true;
		}
		
		return false;
	}
			
	/**
	* 
	*/
	static function _isInProgress($a_status, $a_satisfied)
	{
		if ($a_status != "completed")
		{
			return true;
		}
		
		return false;
	}

	/**
	* 
	*/
	static function _isFailed($a_status, $a_satisfied)
	{
		if ($a_status == "completed" && $a_satisfied == "notSatisfied")
		{
			return true;
		}
		
		return false;
	}


}
// END class.ilSCORM2004Tracking
?>
