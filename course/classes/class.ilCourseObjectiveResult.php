<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* class ilcourseobjective
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/

define('IL_OBJECTIVE_STATUS_PRETEST','pretest');
define('IL_OBJECTIVE_STATUS_FINAL','final');
define('IL_OBJECTIVE_STATUS_NONE','none');
define('IL_OBJECTIVE_STATUS_FINISHED','finished');
define('IL_OBJECTIVE_STATUS_PRETEST_NON_SUGGEST','pretest_non_suggest');


class ilCourseObjectiveResult
{
	var $db = null;
	var $user_id = null;

	
	function ilCourseObjectiveResult($a_usr_id)
	{
		global $ilDB;

		$this->db =& $ilDB;

		$this->user_id = $a_usr_id;
	}
	function getUserId()
	{
		return $this->user_id;
	}

	function getAccomplished($a_crs_id)
	{
		return ilCourseObjectiveResult::_getAccomplished($this->getUserId(),$a_crs_id);
	}
	function _getAccomplished($a_user_id,$a_crs_id)
	{
		global $ilDB;

		include_once 'course/classes/class.ilCourseObjective.php';
		$objectives = ilCourseObjective::_getObjectiveIds($a_crs_id);

		if(!is_array($objectives))
		{
			return array();
		}
		$query = "SELECT objective_id FROM crs_objective_status ".
			"WHERE objective_id IN ('".implode("','",$objectives)."') ".
			"AND status = 1 ".
			"AND user_id = '".$a_user_id."'";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$accomplished[] = $row->objective_id;
		}
		#var_dump("<pre>",$accomplished,"<pre>");
		return $accomplished ? $accomplished : array();
	}

	function getSuggested($a_crs_id)
	{
		return ilCourseObjectiveResult::_getSuggested($this->getUserId(),$a_crs_id);
	}
	function _getSuggested($a_user_id,$a_crs_id)
	{
		global $ilDB;

		$objectives = ilCourseObjective::_getObjectiveIds($a_crs_id);
		$query = "SELECT objective_id FROM crs_objective_status ".
			"WHERE objective_id IN ('".implode("','",$objectives)."') ".
			"AND user_id = '".$a_user_id."'";
		$res = $ilDB->query($query);

		$finished = array();
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$finished[] = $row->objective_id;
		}

		foreach($objectives as $objective_id)
		{
			if(!in_array($objective_id,$finished))
			{
				$suggested[] = $objective_id;
			}
		}
		#var_dump("<pre>",$suggested,$finished,"<pre>");
		return $suggested ? $suggested : array();
	}

	function reset($a_course_id)
	{
		include_once './course/classes/class.ilCourseObjective.php';
		include_once './course/classes/class.ilCourseObjectiveQuestion.php';


		foreach($objectives = ilCourseObjective::_getObjectiveIds($a_course_id) as $objective_id)
		{
			$tmp_obj_question =& new ilCourseObjectiveQuestion($objective_id);
		
			foreach($tmp_obj_question->getTests() as $test_data)
			{
				$this->__deleteEntries($tmp_obj_question->getQuestionsByTest($test_data['ref_id']));
				
				if($tmp_test =& ilObjectFactory::getInstanceByRefId($test_data['ref_id']))
				{
					#$tmp_test->deleteResults($this->getUserId(),true);
					$tmp_test->removeSelectedTestResults(array($this->getUserId()));
					unset($tmp_test);
				}
			}
		}

		if(count($objectives))
		{
			$query = "DELETE FROM crs_objective_status ".
				"WHERE objective_id IN ('".implode("','",$objectives)."') ".
				"AND user_id = '".$this->getUserId()."'";
			$this->db->query($query);
		}

		return true;
	}

	function getStatus($a_course_id)
	{
		include_once 'assessment/classes/class.assQuestion.php';
		include_once 'course/classes/class.ilCourseObjective.php';
		$objective_ids = ilCourseObjective::_getObjectiveIds($a_course_id);
		$objectives = ilCourseObjectiveResult::_readAssignedObjectives($objective_ids);
		$accomplished = $this->getAccomplished($a_course_id);
		$suggested = $this->getSuggested($a_course_id);

		if(count($accomplished) == count($objective_ids))
		{
			return IL_OBJECTIVE_STATUS_FINISHED;
		}

		$all_pretest_answered = true;
		$all_final_answered = true;
		foreach($objectives as $data)
		{
			if(!assQuestion::_areAnswered($this->getUserId(),$data['questions']))
			{
				if($data['tst_status'])
				{
					$all_final_answered = false;
				}
				else
				{
					$all_pretest_answered = false;
				}
			}
		}
		if($all_final_answered)
		{
			#echo 1;
			return IL_OBJECTIVE_STATUS_FINAL;
		}
		if($all_pretest_answered and 
		   !count($suggested))
		{
			#echo 2;
			return IL_OBJECTIVE_STATUS_PRETEST_NON_SUGGEST;
		}
		elseif($all_pretest_answered)
		{
			#echo 3;
			return IL_OBJECTIVE_STATUS_PRETEST;
		}
		#echo 4;
		return IL_OBJECTIVE_STATUS_NONE;
	}

	function hasAccomplishedObjective($a_objective_id)
	{
		$query = "SELECT status FROM crs_objective_status ".
			"WHERE objective_id = '".$a_objective_id."' ".
			"AND user_id = '".$this->getUserId()."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->status == 1 ? true : false;
		}
		return false;
	}

	function readStatus($a_crs_id)
	{
		include_once './course/classes/class.ilCourseObjective.php';

		$objective_ids = ilCourseObjective::_getObjectiveIds($a_crs_id);
		$objectives = ilCourseObjectiveResult::_readAssignedObjectives($objective_ids);
		ilCourseObjectiveResult::_updateObjectiveStatus($this->getUserId(),$objectives);
		return true;
	}
	



	// PRIVATE
	function __deleteEntries($a_objective_ids)
	{
		if(!count($a_objective_ids))
		{
			return true;
		}
		$in = "IN ('";
		$in .= implode("','",$a_objective_ids);
		$in .= "')";

		$query = "DELETE FROM crs_objective_results ".
			"WHERE usr_id = '".$this->getUserId()."' ".
			"AND question_id ".$in;

		$this->db->query($query);

		
	}

	function _deleteUser($user_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_objective_results ".
			"WHERE usr_id = '".$user_id."'";

		$ilDB->query($query);
		
		return true;
	}

	function _updateObjectiveResult($a_user_id,$a_active_id,$a_question_id)
	{
		// find all objectives this question is assigned to
		if(!$objectives = ilCourseObjectiveResult::_readAssignedObjectivesOfQuestion($a_question_id))
		{
			// no objectives found. TODO user has passed a test. After that questions of that test are assigned to an objective.
			// => User has not passed
			return true;
		}
		ilCourseObjectiveResult::_updateObjectiveStatus($a_user_id,$objectives);
		
		return true;
	}

	function _readAssignedObjectivesOfQuestion($a_question_id)
	{
		global $ilDB;

		// get all objtives and questions this current question is assigned to
		$query = "SELECT q2.question_id as qid,q2.objective_id as ob FROM crs_objective_qst as q1, ".
			"crs_objective_qst as q2 ".
			"WHERE q1.question_id = '".$a_question_id."' ".
			"AND q1.objective_id = q2.objective_id ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objectives['all_objectives'][$row->ob] = $row->ob;
			$objectives['all_questions'][$row->qid] = $row->qid;
		}
		if(!is_array($objectives))
		{
			return false;
		}
		$objectives['objectives'] = ilCourseObjectiveResult::_readAssignedObjectives($objectives['all_objectives']);
		return $objectives ? $objectives : array();
	}


	function _readAssignedObjectives($a_all_objectives)
	{
		global $ilDB;

		// Read necessary points
		$query = "SELECT t.objective_id as obj,t.ref_id as ref, question_id,tst_status,tst_limit ".
			"FROM crs_objective_tst as t JOIN crs_objective_qst as q ".
			"ON (t.objective_id = q.objective_id AND t.ref_id = q.ref_id) ".
			"WHERE t.objective_id IN ('".implode("','",$a_all_objectives)."')";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objectives[$row->obj."_".$row->ref]['questions'][$row->question_id] = $row->question_id;
			$objectives[$row->obj."_".$row->ref]['tst_status'] = $row->tst_status;
			$objectives[$row->obj."_".$row->ref]['tst_limit'] = $row->tst_limit;
			$objectives[$row->obj."_".$row->ref]['objective_id'] = $row->obj;
		}
		return $objectives ? $objectives : array();
	}

	function _updateObjectiveStatus($a_user_id,$objectives)
	{
		global $ilDB,$ilUser;

		if(!count($objectives['all_questions']) or
		   !count($objectives['all_objectives']))
		{
			return false;
		}

		// Read reachable points
		$query = "SELECT question_id,points FROM qpl_questions ".
			"WHERE question_id IN('".implode("','",$objectives['all_questions'])."')";
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objectives['all_question_points'][$row->question_id]['max_points'] = $row->points;
		}
		// Read reached points
		$query = "SELECT question_fi, MAX(points) as reached FROM tst_test_result JOIN tst_active ".
			"ON (active_id = active_fi) ".
			"WHERE user_fi = '".$a_user_id."' ".
			"AND question_fi IN ('".implode("','",$objectives['all_questions'])."') ".
			"GROUP BY question_fi,user_fi";
		#var_dump("<pre>",$objectives,"<pre>");
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objectives['all_question_points'][$row->question_fi]['reached_points'] = $row->reached;
		}

		// Check accomplished
		foreach($objectives['objectives'] as $kind => $data)
		{
			// objective does not allow to change status
			if(ilCourseObjectiveResult::__isFullfilled($objectives['all_question_points'],$data))
			{
				// Status 0 means pretest fullfilled, status 1 means final test fullfilled
				$fullfilled[] = array($data['objective_id'],$ilUser->getId(),$data['tst_status']);
			}
		}
		if(is_array($fullfilled))
		{
			$ilDB->executeMultiple($ilDB->prepare("REPLACE INTO crs_objective_status VALUES(?,?,?)"),
								   $fullfilled);
		}

		#ilCourseObjectiveResult::__updatePassed($objectives['all_objectives']);
		return true;
	}

	function __isFullfilled($question_points,$objective_data)
	{
		if(!is_array($objective_data['questions']))
		{
			return false;
		}
		$max_points = 0;
		$reached_points = 0;
		foreach($objective_data['questions'] as $question_id)
		{
			$max_points += $question_points[$question_id]['max_points'];
			$reached_points += $question_points[$question_id]['reached_points'];
		}
		if(!$max_points)
		{
			return false;
		}
		return (($reached_points / $max_points * 100) >= $objective_data['tst_limit']) ? true : false;
	}
		
}
?>