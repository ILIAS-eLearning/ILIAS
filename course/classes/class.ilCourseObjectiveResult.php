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

	function reset($a_course_id)
	{
		include_once './course/classes/class.ilCourseObjective.php';
		include_once './course/classes/class.ilCourseObjectiveQuestion.php';


		foreach(ilCourseObjective::_getObjectiveIds($a_course_id) as $objective_id)
		{
			$tmp_obj_question =& new ilCourseObjectiveQuestion($objective_id);
		
			foreach($tmp_obj_question->getTests() as $test_data)
			{
				$this->__deleteEntries($tmp_obj_question->getQuestionsByTest($test_data['ref_id']));
				
				if($tmp_test =& ilObjectFactory::getInstanceByRefId($test_data['ref_id']))
				{
					$tmp_test->deleteResults($this->getUserId(),true);
					unset($tmp_test);
				}
			}
		}

		// unset hashed accomplished
		unset($_SESSION['accomplished']);
		unset($_SESSION['objectives_suggested']);
		unset($_SESSION['objectives_status']);
		unset($_SESSION['objectives_fullfilled']);

		return true;
	}

	function getStatus($a_course_id)
	{
		include_once './course/classes/class.ilCourseObjective.php';
		include_once './course/classes/class.ilCourseObjectiveQuestion.php';

		$final = false;
		$pretest = false;

		foreach(ilCourseObjective::_getObjectiveIds($a_course_id) as $objective_id)
		{
			$tmp_obj_question =& new ilCourseObjectiveQuestion($objective_id);
		
			foreach($tmp_obj_question->getTests() as $test_data)
			{
				if($this->__isAnswered($tmp_obj_question->getQuestionsByTest($test_data['ref_id'])))
				{
					if($test_data['tst_status'])
					{
						$final = true;
					}
					else
					{
						$pretest = true;
					}
				}
			}
		}
		if($final)
		{
			return 'final';
		}
		if($pretest)
		{
			return 'pretest';
		}
		return 'none';
	}

	function updateResults($a_test_result)
	{
		foreach($a_test_result as $question_data)
		{
			if($question_data['qid'])
			{
				$this->addEntry($question_data['qid'],$question_data['reached']);
			}
		}
		// unset hashed accomplished
		unset($_SESSION['accomplished']);
		unset($_SESSION['objectives_suggested']);
		unset($_SESSION['objectives_status']);

		return true;
	}

	function isSuggested($a_objective_id)
	{
		$suggested = true;
		$edited_final = true;

		include_once './course/classes/class.ilCourseObjectiveQuestion.php';
		include_once './assessment/classes/class.ilObjTest.php';
		include_once './assessment/classes/class.ilObjTestAccess.php';


		$tmp_obj_question =& new ilCourseObjectiveQuestion($a_objective_id);

		foreach($tmp_obj_question->getTests() as $test_data)
		{
			$tmp_points = $this->__getReachedPoints($tmp_obj_question->getQuestionsByTest($test_data['ref_id']));
			$max = $tmp_obj_question->getMaxPointsByTest($test_data['ref_id']);
			if(!$max)
			{
				return false;
			}
			if($test_data['tst_status'])
			{
				if(ilObjTestAccess::_hasFinished($this->getUserId(),$test_data['obj_id']))
				{
					return true;
				}
				continue;
			}
			if(!$tmp_points)
			{
				$suggested = true;
				continue;
			}
			$percent = ($tmp_points / $max) * 100.0;
			
			if($percent < $test_data['tst_limit'])
			{
				$suggested = true;
			}
			else
			{
				$suggested = false;
			}
		}
		return $suggested;
	}


	function hasAccomplishedObjective($a_objective_id)
	{
		$reached = 0;
		$accomplished = true;

		include_once './course/classes/class.ilCourseObjectiveQuestion.php';

		$tmp_obj_question =& new ilCourseObjectiveQuestion($a_objective_id);

		foreach($tmp_obj_question->getTests() as $test_data)
		{
			$tmp_points = $this->__getReachedPoints($tmp_obj_question->getQuestionsByTest($test_data['ref_id']));

			$max = $tmp_obj_question->getMaxPointsByTest($test_data['ref_id']);

			if(!$max)
			{
				continue;
			}
			if(!$tmp_points)
			{
				if($test_data['tst_status'])
				{
					return false;
				}
				else
				{
					$accomplished = false;
					continue;
				}
			}

			$percent = ($tmp_points / $max) * 100.0;

			if($percent >= $test_data['tst_limit'] and $test_data['tst_status'])
			{
				return true;
			}
			// no fullfilled
			if($test_data['tst_status'])
			{
				return false;
			}
			$accomplished = false;
				
		}
		return $accomplished ? true : false;
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


	function __getReachedPoints($a_question_ids)
	{
		$points = 0;

		foreach($a_question_ids as $qid)
		{
			$query = "SELECT points FROM crs_objective_results ".
				"WHERE usr_id = '".$this->getUserId()."' ".
				"AND question_id = '".$qid."'";

			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$points += ((int) $row->points);
			}
		}
		return $points ? $points : 0;
	}

	function __isAnswered($a_question_ids)
	{
		foreach($a_question_ids as $qid)
		{

			$query = "SELECT points FROM crs_objective_results ".
				"WHERE usr_id = '".$this->getUserId()."' ".
				"AND question_id = '".$qid."'";

			$res = $this->db->query($query);
			while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
			{
				return true;
			}
		}
		return false;
	}		

	function _updateUserResult($a_usr_id,$a_question_id,$a_points)
	{
		global $ilDB;

		// Delete old entry
		$query = "DELETE FROM crs_objective_results ".
			"WHERE usr_id = '".$a_usr_id."' ".
			"AND question_id = '".$a_question_id."'";

		$ilDB->query($query);
		
		// ... and add it
		$query = "INSERT INTO crs_objective_results ".
			"SET usr_id = '".$a_usr_id."', ".
			"question_id = '".$a_question_id."', ".
			"points = '".$a_points."'";

		$ilDB->query($query);

		return true;
	}



	function addEntry($a_question_id,$a_points)
	{
		// DElete old entry
		$query = "DELETE FROM crs_objective_results ".
			"WHERE usr_id = '".$this->getUserId()."' ".
			"AND question_id = '".$a_question_id."'";

		$this->db->query($query);

		$query = "INSERT INTO crs_objective_results ".
			"SET usr_id = '".$this->getUserId()."', ".
			"question_id = '".$a_question_id."', ".
			"points = '".$a_points."'";

		$this->db->query($query);

		return true;
	}


	function _deleteUser($user_id)
	{
		global $ilDB;

		$query = "DELETE FROM crs_objective_results ".
			"WHERE usr_id = '".$user_id."'";

		$ilDB->query($query);
		
		return true;
	}
}
?>