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
*/

class ilCourseObjectiveQuestion
{
	const TYPE_SELF_ASSESSMENT = 0;
	const TYPE_FINAL_TEST = 1;
	
	public $db = null;

	public $objective_id = null;
	public $questions;
	protected $tests = array();

	function ilCourseObjectiveQuestion($a_objective_id)
	{
		global $ilDB;

		$this->db =& $ilDB;
	
		$this->objective_id = $a_objective_id;

		$this->__read();
	}
	
	
	/**
	 * Check if test is assigned to objective
	 *
	 * @access public
	 * @static
	 *
	 * @param int test ref_id
	 * @param int objective_id
	 * @return boolean success
	 */
	public static function _isTestAssignedToObjective($a_test_id,$a_objective_id)
	{
		global $ilDB;
		
		$query = "SELECT qst_ass_id FROM crs_objective_qst ".
			"WHERE ref_id = ".$ilDB->quote($a_test_id)." ".
			"AND objective_id = ".$ilDB->quote($a_objective_id);
		$res = $ilDB->query($query);
		return $res->numRows() ? true : false;
	}
	
	/**
	 * clone objective questions
	 *
	 * @access public
	 *
	 * @param int source objective
	 * @param int target objective
	 * @param int copy id
	 */
	public function cloneDependencies($a_new_objective,$a_copy_id)
	{
		global $ilObjDataCache,$ilLog;
		
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		$cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
		$mappings = $cwo->getMappings();
		foreach($this->getQuestions() as $question)
		{
			if(!isset($mappings["$question[ref_id]"]) or !$mappings["$question[ref_id]"])
			{
				continue;
			}
			$question_ref_id = $question['ref_id'];
			$question_obj_id = $question['obj_id'];
			$question_qst_id = $question['question_id'];
			$new_ref_id = $mappings[$question_ref_id];
			$new_obj_id = $ilObjDataCache->lookupObjId($new_ref_id);
			
			if($new_obj_id == $question_obj_id)
			{
				$ilLog->write(__METHOD__.': Test has been linked. Keeping question id.');
				// Object has been linked
				$new_question_id = $question_qst_id;
			}
			else
			{
				$new_question_info = $mappings[$question_ref_id.'_'.$question_qst_id];
				$new_question_arr = explode('_',$new_question_info);
				if(!isset($new_question_arr[1]) or !$new_question_arr[1])
				{
					continue;
				}
				$new_question_id = $new_question_arr[1];
				$ilLog->write(__METHOD__.': New question id is: '.$new_question_id);
			}
	
			$new_question = new ilCourseObjectiveQuestion($a_new_objective);
			$new_question->setTestRefId($new_ref_id);
			$new_question->setTestObjId($new_obj_id);
			$new_question->setQuestionId($new_question_id);
			$new_question->add();
		}
		
		// Copy tests
		foreach($this->getTests() as $test)
		{
			$new_test_id = $mappings["$test[ref_id]"];
			
			$query = "UPDATE crs_objective_tst ".
				"SET tst_status = ".$this->db->quote($test['tst_status']).", ".
				"tst_limit = ".$this->db->quote($test['tst_limit'])." ".
				"WHERE objective_id = ".$this->db->quote($a_new_objective)." ".
				"AND ref_id = ".$this->db->quote($new_test_id);
			$this->db->query($query);
		}
	}
	
	/**
	 * Get assignable tests
	 *
	 * @access public
	 * @static
	 *
	 * @param
	 */
	public static function _getAssignableTests($a_container_ref_id)
	{
		global $tree;
		
		return $tree->getSubTree($tree->getNodeData($a_container_ref_id),true,'tst');
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
		global $ilDB;
		
		$query = "UPDATE crs_objective_tst ".
			"SET tst_status = ".$this->db->quote($this->getTestStatus())." ".
			"WHERE objective_id = ".$this->db->quote($this->getObjectiveId())." ".
			"AND ref_id = ".$this->db->quote($this->getTestRefId())." ";
		$this->db->query($query);
		

		// CHECK if entry already exists
		$query = "SELECT * FROM crs_objective_tst ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ".
			"AND ref_id = ".$ilDB->quote($this->getTestRefId())."";

		$res = $this->db->query($query);
		if($res->numRows())
		{
			return false;
		}
		
		// Check for existing limit
		$query = "SELECT tst_limit FROM crs_objective_tst ".
			"WHERE objective_id = ".$this->db->quote($this->getObjectiveId())." ".
			"AND tst_status = ".$this->db->quote($this->getTestStatus())." ";
			
		$res = $this->db->query($query);
		
		$limit = -1;
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$limit = $row->tst_limit;
		}
		
		$query = "INSERT INTO crs_objective_tst ".
			"SET objective_id = ".$ilDB->quote($this->getObjectiveId()).", ".
			"ref_id = ".$ilDB->quote($this->getTestRefId()).", ".
			"obj_id = ".$ilDB->quote($this->getTestObjId()).", ".
			"tst_status = ".$ilDB->quote($this->getTestStatus()).", ".
			"tst_limit = ".$this->db->quote($limit)." ";

		$this->db->query($query);

		return true;
	}

	function __deleteTest($a_test_ref_id)
	{
		global $ilDB;
		
		// Delete questions
		$query = "DELETE FROM crs_objective_qst ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ".
			"AND ref_id = ".$ilDB->quote($a_test_ref_id)." ";

		$this->db->query($query);

		// delete tst entries
		$query = "DELETE FROM crs_objective_tst ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ".
			"AND ref_id = ".$ilDB->quote($a_test_ref_id)." ";

		$this->db->query($query);

		unset($this->tests[$a_test_ref_id]);

		return true;
	}

	function updateTest($a_test_objective_id)
	{
		global $ilDB;
		
		$query = "UPDATE crs_objective_tst ".
			"SET tst_status = ".$ilDB->quote($this->getTestStatus()).", ".
			"tst_limit = ".$ilDB->quote($this->getTestSuggestedLimit())." ".
			"WHERE test_objective_id = ".$ilDB->quote($a_test_objective_id)."";

		$this->db->query($query);

		return true;
	}

	function getTests()
	{
		global $ilDB;
		
		$query = "SELECT * FROM crs_objective_tst as cot ".
			"JOIN object_data as obd ON cot.obj_id = obd.obj_id ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ".
			"ORDER BY title ";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$test['test_objective_id'] = $row->test_objective_id;
			$test['objective_id']		= $row->objective_id;
			$test['ref_id']			= $row->ref_id;
			$test['obj_id']			= $row->obj_id;
			$test['tst_status']		= $row->tst_status;
			$test['tst_limit']		= $row->tst_limit;
			$test['title']			= $row->title;

			$tests[] = $test;
		}

		return $tests ? $tests : array();
	}
	
	/**
	 * get self assessment tests
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getSelfAssessmentTests()
	{
		foreach($this->tests as $test)
		{
			if($test['status'] == self::TYPE_SELF_ASSESSMENT)
			{
				$self[] = $test;
			}
		}
		return $self ? $self : array();
	}
	
	/**
	 * get final tests
	 *
	 * @access public
	 * @return
	 */
	public function getFinalTests()
	{
		foreach($this->tests as $test)
		{
			if($test['status'] == self::TYPE_FINAL_TEST)
			{
				$final[] = $test;
			}
		}
		return $final ? $final : array();
	}
	
	function _getTest($a_test_objective_id)
	{
		global $ilDB;

		$query = "SELECT * FROM crs_objective_tst ".
			"WHERE test_objective_id = ".$ilDB->quote($a_test_objective_id)." ";

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
	
	/**
	 * get self assessment questions
	 *
	 * @access public
	 * @return
	 */
	public function getSelfAssessmentQuestions()
	{
		foreach($this->questions as $question)
		{
			if($question['test_type'] == self::TYPE_SELF_ASSESSMENT)
			{
				$self[] = $question; 
			}
		}
		return $self ? $self : array();
	}

	/**
	 * get self assessment points
	 *
	 * @access public
	 * @return
	 */
	public function getSelfAssessmentPoints()
	{
		foreach($this->getSelfAssessmentQuestions() as $question)
		{
			$points += $question['points'];
		}
		return $points ? $points : 0;
	}
	
	/**
	 * get final test points
	 *
	 * @access public
	 * @return
	 */
	public function getFinalTestPoints()
	{
		foreach($this->getFinalTestQuestions() as $question)
		{
			$points += $question['points'];
		}
		return $points ? $points : 0;
	}
	
	/**
	 * check if question is self assessment question
	 * @param int question id
	 * @access public
	 * @return
	 */
	public function isSelfAssessmentQuestion($a_question_id)
	{
		foreach($this->questions as $question)
		{
			if($question['question_id'] == $a_question_id)
			{
				return $question['test_type'] == self::TYPE_SELF_ASSESSMENT;
			}
		}
		return false;
	}
	
	/**
	 * is final test question
	 *
	 * @access public
	 * @param int question id
	 * @return
	 */
	public function isFinalTestQuestion($a_question_id)
	{
		foreach($this->questions as $question)
		{
			if($question['question_id'] == $a_question_id)
			{
				return $question['test_type'] == self::TYPE_FINAL_TEST;
			}
		}
		return false;
		
	}
	
	/**
	 * get final test questions
	 *
	 * @access public
	 * @return
	 */
	public function getFinalTestQuestions()
	{
		foreach($this->questions as $question)
		{
			if($question['test_type'] == self::TYPE_FINAL_TEST)
			{
				$final[] = $question; 
			}
		}
		return $final ? $final : array();
	}
	
	
	
	/**
	 * Get questions of test
	 *
	 * @access public
	 * @param int test id
	 * 
	 */
	public function getQuestionsOfTest($a_test_id)
	{
	 	foreach($this->getQuestions() as $qst)
	 	{
	 		if($a_test_id == $qst['obj_id'])
	 		{
	 			$questions[] = $qst;
	 		}
	 	}
	 	return $questions ? $questions : array();
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
		include_once './Modules/Test/classes/class.ilObjTest.php';

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
	
	/**
	 * lookup maximimum point
	 *
	 * @access public
	 * @param int question id
	 * @return
	 * @static
	 */
	public static function _lookupMaximumPointsOfQuestion($a_question_id)
	{
		include_once('Modules/TestQuestionPool/classes/class.assQuestion.php');
		return assQuestion::_getMaximumPoints($a_question_id);
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

	/**
	 * update limits
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateLimits()
	{
		foreach($this->tests as $ref_id => $test_data)
		{
			switch($test_data['status'])
			{
				case self::TYPE_SELF_ASSESSMENT:
					$points = $this->getSelfAssessmentPoints();
					break;
				
				case self::TYPE_FINAL_TEST:
					$points = $this->getFinalTestPoints();
					break;
			}
			if($test_data['limit'] == -1 or $test_data['limit'] > $points)
			{
				switch($test_data['status'])
				{
					case self::TYPE_SELF_ASSESSMENT:
						$points = $this->getSelfAssessmentPoints();
						break;
					
					case self::TYPE_FINAL_TEST:
						$points = $this->getFinalTestPoints();
						break;
				}
				$query = "UPDATE crs_objective_tst ".
					"SET tst_limit = ".$this->db->quote($points)." ".
					"WHERE test_objective_id = ".$this->db->quote($test_data['test_objective_id'])." ";
				$this->db->query($query);
			}
		}
	}


	function add()
	{
		global $ilDB;
		
		$query = "DELETE FROM crs_objective_qst ".
			"WHERE objective_id = ".$this->db->quote($this->getObjectiveId())." ".
			"AND question_id = ".$this->db->quote($this->getQuestionId())." ";
		$this->db->query($query);
		
		
		$query = "INSERT INTO crs_objective_qst ".
			"SET objective_id = ".$ilDB->quote($this->getObjectiveId()).", ".
			"ref_id = ".$ilDB->quote($this->getTestRefId()).", ".
			"obj_id = ".$ilDB->quote($this->getTestObjId()).", ".
			"question_id = ".$ilDB->quote($this->getQuestionId())."";

		$this->db->query($query);

		$this->__addTest();
		
		$this->__read();

		return true;
	}
	function delete($qst_id)
	{
		global $ilDB;
		
		if(!$qst_id)
		{
			return false;
		}
		
		$query = "SELECT * FROM crs_objective_qst ".
			"WHERE qst_ass_id = ".$ilDB->quote($qst_id)." ";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$test_rid = $row->ref_id;
			$test_oid = $row->obj_id;
		}

		$query = "DELETE FROM crs_objective_qst ".
			"WHERE qst_ass_id = ".$ilDB->quote($qst_id)." ";

		$this->db->query($query);

		// delete test if it was the last question
		$query = "SELECT * FROM crs_objective_qst ".
			"WHERE ref_id = ".$ilDB->quote($test_rid)." ".
			"AND obj_id = ".$ilDB->quote($test_oid)." ".
			"AND objective_id = ".$ilDB->quote($this->getObjectiveId())." ";
		

		$res = $this->db->query($query);
		if(!$res->numRows())
		{
			$this->__deleteTest($test_rid);
		}

		return true;
	}

	function deleteAll()
	{
		global $ilDB;
		
		$query = "DELETE FROM crs_objective_qst ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ";

		$this->db->query($query);

		$query = "DELETE FROM crs_objective_tst ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ";

		$this->db->query($query);

		return true;
	}


	// PRIVATE
	function __read()
	{
		global $ilDB,$tree;
		
		include_once './Modules/Test/classes/class.ilObjTest.php';
		include_once('Modules/Course/classes/class.ilCourseObjective.php');

		$container_ref_ids = ilObject::_getAllReferences(ilCourseObjective::_lookupContainerIdByObjectiveId($this->objective_id));
		$container_ref_id  = current($container_ref_ids);
		
		// Read test data
		$query = "SELECT * FROM crs_objective_tst ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ";
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->tests[$row->ref_id]['test_objective_id'] = $row->test_objective_id;
			$this->tests[$row->ref_id]['ref_id'] = $row->ref_id;
			$this->tests[$row->ref_id]['obj_id'] = $row->obj_id;
			$this->tests[$row->ref_id]['status'] = $row->tst_status;
			$this->tests[$row->ref_id]['limit'] = $row->tst_limit;
		}

		$this->questions = array();
		$query = "SELECT * FROM crs_objective_qst as coq ".
			"JOIN qpl_questions as qq ON coq.question_id = qq.question_id ".
			"WHERE objective_id = ".$ilDB->quote($this->getObjectiveId())." ".
			"ORDER BY title";

		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if(!$tree->isInTree($row->ref_id) or !$tree->isGrandChild($container_ref_id,$row->ref_id))
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
			$qst['title'] = $question->getTitle();
			$qst['description'] = $question->getComment();
			$qst['test_type'] = $this->tests[$row->ref_id]['status'];
			$qst['points'] = $question->getPoints();

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
			"AND crs_qst.objective_id = ".$ilDB->quote($a_objective_id) ." ".
			"AND ref_id = ".$ilDB->quote($a_tst_ref_id)." ".
			"AND question_id = ".$ilDB->quote($a_question_id)." ";

		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$objective_id = $row->objective_id;
		}
		
		return $objective_id ? $objective_id : 0;
	}

}
?>