<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/TestQuestionPool/interfaces/interface.ilQuestionChangeListener.php';

/**
 * Listener for question changes
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/Test
 */
class ilDynamicTestQuestionChangeListener implements ilQuestionChangeListener
{
	/**
	 * @var ilDB
	 */
	protected $db = null;
	
	/**
	 * @param ilDB $db
	 */
	public function __construct(ilDB $db)
	{
		$this->db = $db;
	}
	
	/**
	 * @var array[integer]
	 */
	private $testObjIds = array();
	
	/**
	 * @param integer $testObjId
	 */
	public function addTestObjId($testObjId)
	{
		$this->testObjIds[] = $testObjId;
	}
	
	/**
	 * @return array[integer]
	 */
	public function getTestObjIds()
	{
		return $this->testObjIds;
	}

	/**
	 * @param assQuestion $question
	 */
	public function notifyQuestionCreated(assQuestion $question)
	{
		//mail('bheyser@databay.de', __METHOD__, __METHOD__);
		// nothing to do
	}

	/**
	 * @param assQuestion $question
	 */
	public function notifyQuestionEdited(assQuestion $question)
	{
		//mail('bheyser@databay.de', __METHOD__, __METHOD__);
		$this->deleteTestsParticipantsResultsForQuestion($question);
	}
	
	public function notifyQuestionDeleted(assQuestion $question)
	{
		//mail('bheyser@databay.de', __METHOD__, __METHOD__);
		$this->deleteTestsParticipantsResultsForQuestion($question);
	}
	
	/**
	 * @param assQuestion $question
	 */
	public function deleteTestsParticipantsResultsForQuestion(assQuestion $question)
	{
		$activeIds = $this->getActiveIds();
		
		if( !count($activeIds) )
		{
			return null;
		}
		
		$inActiveIds = $this->db->in('active_fi', $activeIds, false, 'integer');
		
		$this->db->manipulateF(
				"DELETE FROM tst_solutions WHERE question_fi = %s AND $inActiveIds",
				array('integer'), array($question->getId())
		);
		
		$this->db->manipulateF(
				"DELETE FROM tst_qst_solved WHERE question_fi = %s AND $inActiveIds",
				array('integer'), array($question->getId())
		);
		
		$this->db->manipulateF(
				"DELETE FROM tst_test_result WHERE question_fi = %s AND $inActiveIds",
				array('integer'), array($question->getId())
		);
		
		$this->db->manipulate("DELETE FROM tst_pass_result WHERE $inActiveIds");
		
		$this->db->manipulate("DELETE FROM tst_result_cache WHERE $inActiveIds");
	}
	
	private function getActiveIds()
	{
		if( !count($this->getTestObjIds()) )
		{
			return null;
		}
		
		$inTestObjIds = $this->db->in('obj_fi', $this->getTestObjIds(), false, 'integer');
		
		$res = $this->db->query("
			SELECT active_id
			FROM tst_tests
			INNER JOIN tst_active
			ON test_fi = test_id
			WHERE $inTestObjIds
		");
		
		$activeIds = array();
		
		while( $row = $this->db->fetchAssoc($res) )
		{
			$activeIds[] = $row['active_id'];
		}
		
		return $activeIds;
	}
}
