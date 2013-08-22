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
class ilTestQuestionChangeListener implements ilQuestionChangeListener
{
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
	public function deleteAllParticipantsResultsForQuestion(assQuestion $question)
	{
		
	}

	/**
	 * @param assQuestion $question
	 */
	public function notifyQuestionEdited(assQuestion $question)
	{
		//mail('bheyser@databay.de', __METHOD__, __METHOD__);
		$this->deleteAllParticipantsResultsForQuestion($question);
	}
	
	public function notifyQuestionDeleted(assQuestion $question)
	{
		//mail('bheyser@databay.de', __METHOD__, __METHOD__);
		$this->deleteAllParticipantsResultsForQuestion($question);
	}
}
