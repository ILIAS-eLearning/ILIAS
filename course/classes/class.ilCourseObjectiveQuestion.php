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
* class ilcourseobjectiveQuestion
*
* @author Stefan Meyer <smeyer@databay.de> 
* @version $Id$
* 
* @extends Object
* @package ilias-core
*/

class ilCourseObjectiveQuestion
{
	var $db = null;

	var $objective_id = null;
	var $questions;

	function ilCourseObjectiveQuestion($a_objective_id)
	{
		global $ilDB;

		$this->db =& $ilDB;
	
		$this->objective_id = $a_objective_id;

		$this->__read();
	}

	// ########################################################  Methods for test table
	function setTestStatus($a_status)
	{
		$this->tst_status = $a_status;
	}
	function getTestStatus()
	{
		return (int) $this->tst_status;
	}
	function setTestSuggestedLimit($a_limit)
	{
		$this->tst_limit = $a_limit;
	}
	function getTestSuggestedLimit()
	{
		return (int) $this->tst_limit;
	}
	function __addTest()
	{
		// CHECK if entry already exists
		$query = "SELECT * FROM crs_objective_tst ".
			"WHERE objective_id = '".$this->getObjectiveId()."' ".
			"AND ref_id = '".$this->getTestRefId()."'";

		$res = $this->db->query($query);
		if($res->numRows())
		{
			return false;
		}
		$query = "INSERT INTO crs_objective_tst ".
			"SET objective_id = '".$this->getObjectiveId()."', ".
			"ref_id = '".$this->getTestRefId()."', ".
			"obj_id = '".$this->getTestObjId()."', ".
			"tst_status = '".$this->getTestStatus()."', ".
			"tst_limit = '100'";

		$this->db->query($query);

		return true;
	}

	function __deleteTest($a_test_ref_id)
	{
		// Delete questions
		$query = "DELETE FROM crs_objective_qst ".
			"WHERE objective_id = '".$this->getObjectiveId()."' ".
			"AND ref_id = '".$a_test_ref_id."'";

		$this->db->query($query);

		// delete tst entries
		$query = "DELETE FROM crs_objective_tst ".
			"WHERE objective_id = '".$this->getObjectiveId()."' ".
			"AND ref_id = '".$a_test_ref_id."'";

		$this->db->query($query);

		return true;
	}

	function updateTest($a_test_objective_id)
	{
		$query = "UPDATE crs_objective_tst ".
			"SET tst_status = '".$this->getTestStatus()."', ".
			"tst_limit = '".$this->getTestSuggestedLimit()."' ".
			"WHERE test_objective_id = '".$a_test_objective_id."'";

		$this->db->query($query);

		return true;
	}

	function getTests()
	{
		$query = "SELECT * FROM crs_objective_tst ".
			"WHERE objective_id = '".$this->getObjectiveId()."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$test['test_objective_id'] = $row->test_objective_id;
			$test['objective_id']		= $row->objective_id;
			$test['ref_id']			= $row->ref_id;
			$test['obj_id']			= $row->obj_id;
			$test['tst_status']		= $row->tst_status;
			$test['tst_limit']		= $row->tst_limit;

			$tests[] = $test;
		}

		return $tests ? $tests : array();
	}
	
	function _getTest($a_test_objective_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_objective_tst ".
			"WHERE test_objective_id = '".$a_test_objective_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$test['test_objective_id'] = $row->test_objective_id;
			$test['objective_id']		= $row->objective_id;
			$test['ref_id']			= $row->ref_id;
			$test['obj_id']			= $row->obj_id;
			$test['tst_status']		= $row->tst_status;
			$test['tst_limit']		= $row->tst_limit;
		}

		return $test ? $test : array();
	}

	// ############################################################# METHODS for question table
	function getQuestions()
	{
		return $this->questions ? $this->questions : array();
	}
	
	function getQuestion($question_id)
	{
		return $this->questions[$question_id] ? $this->questions[$question_id] : array();
	}

	function getObjectiveId()
	{
		return $this->objective_id;
	}

	function setTestRefId($a_ref_id)
	{
		$this->tst_ref_id = $a_ref_id;
	}
	function getTestRefId()
	{
		return $this->tst_ref_id ? $this->tst_ref_id : 0;
	}
	function setTestObjId($a_obj_id)
	{
		$this->tst_obj_id = $a_obj_id;
	}
	function getTestObjId()
	{
		return $this->tst_obj_id ? $this->tst_obj_id : 0;
	}
	function setQuestionId($a_question_id)
	{
		$this->question_id = $a_question_id;
	}
	function getQuestionId()
	{
		return $this->question_id;
	}


	function getMaxPointsByObjective()
	{
		include_once './assessment/classes/class.ilObjTest.php';

		$points = 0;
		foreach($this->getQuestions() as $question)
		{
			$tmp_test =& ilObjectFactory::getInstanceByRefId($question['ref_id']);

			$tmp_question =& ilObjTest::_instanciateQuestion($question['question_id']);

			$points += $tmp_question->getMaximumPoints();

			unset($tmp_question);
			unset($tmp_test);
		}
		return $points;
	}
	
	function getMaxPointsByTest($a_test_ref_id)
	{
		$points = 0;

		$tmp_test =& ilObjectFactory::getInstanceByRefId($a_test_ref_id);

		foreach($this->getQuestions() as $question)
		{
			if($question['ref_id'] == $a_test_ref_id)
			{
				$tmp_question =& ilObjTest::_instanciateQuestion($question['question_id']);

				$points += $tmp_question->getMaximumPoints();

				unset($tmp_question);
			}
		}
		unset($tmp_test);

		return $points;
	}

	function getNumberOfQuestionsByTest($a_test_ref_id)
	{
		$counter = 0;

		foreach($this->getQuestions() as $question)
		{
			if($question['ref_id'] == $a_test_ref_id)
			{
				++$counter;
			}
		}
		return $counter;
	}

	function getQuestionsByTest($a_test_ref_id)
	{
		foreach($this->getQuestions() as $question)
		{
			if($question['ref_id'] == $a_test_ref_id)
			{
				$qst[] = $question['question_id'];
			}
		}
		return $qst ? $qst : array();
	}


	function add()
	{
		$query = "INSERT INTO crs_objective_qst ".
			"SET objective_id = '".$this->getObjectiveId()."', ".
			"ref_id = '".$this->getTestRefId()."', ".
			"obj_id = '".$this->getTestObjId()."', ".
			"question_id = '".$this->getQuestionId()."'";

		$this->db->query($query);

		$this->__addTest();

		return true;
	}
	function delete($qst_id)
	{
		if(!$qst_id)
		{
			return false;
		}
		
		$query = "SELECT * FROM crs_objective_qst ".
			"WHERE qst_ass_id = '".$qst_id."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$test_rid = $row->ref_id;
			$test_oid = $row->obj_id;
		}

		$query = "DELETE FROM crs_objective_qst ".
			"WHERE qst_ass_id = '".$qst_id."'";

		$this->db->query($query);

		// delete test if it was the last question
		$query = "SELECT * FROM crs_objective_qst ".
			"WHERE ref_id = '".$test_rid."' ".
			"AND obj_id = '".$test_oid."'";

		$res = $this->db->query($query);
		if(!$res->numRows())
		{
			$this->__deleteTest($test_rid);
		}

		return true;
	}

	function deleteAll()
	{
		$query = "DELETE FROM crs_objective_qst ".
			"WHERE objective_id = '".$this->getObjectiveId()."'";

		$this->db->query($query);

		$query = "DELETE FROM crs_objective_tst ".
			"WHERE objective_id = '".$this->getObjectiveId()."'";

		$this->db->query($query);

		return true;
	}


	// PRIVATE
	function __read()
	{
		include_once './assessment/classes/class.ilObjTest.php';

		global $tree;

		$this->questions = array();
		$query = "SELECT * FROM crs_objective_qst ".
			"WHERE objective_id = '".$this->getObjectiveId()."'";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!$tree->isInTree($row->ref_id))
			{
				$this->__deleteTest($row->ref_id);
				continue;
			}
			if(!$question = ilObjTest::_instanciateQuestion($row->question_id))
			{
				$this->delete($row->question_id);
				continue;
			}
			$qst['ref_id'] = $row->ref_id;
			$qst['obj_id'] = $row->obj_id;
			$qst['question_id'] = $row->question_id;
			$qst['qst_ass_id'] = $row->qst_ass_id;

			$this->questions[$row->qst_ass_id] = $qst;
		}
		return true;
	}

	// STATIC
	function _isAssigned($a_objective_id,$a_tst_ref_id,$a_question_id)
	{
		global $ilDB;

		$query = "SELECT crs_qst.objective_id as objective_id FROM crs_objective_qst as crs_qst, crs_objectives as crs_obj ".
			"WHERE crs_qst.objective_id = crs_obj.objective_id ".
			"AND crs_qst.objective_id = '".$a_objective_id ."' ".
			"AND ref_id = '".$a_tst_ref_id."' ".
			"AND question_id = '".$a_question_id."'";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objective_id = $row->objective_id;
		}
		
		return $objective_id ? $objective_id : 0;
	}

}
?>