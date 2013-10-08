<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestQuestionSetConfig.php';

/**
 * class that manages/holds the data for a question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package		Modules/Test
 */
class ilTestFixedQuestionSetConfig extends ilTestQuestionSetConfig
{
	/**
	 * returns the fact wether a useable question set config exists or not
	 * 
	 * @return boolean
	 */
	public function isQuestionSetConfigured()
	{
		if( count($this->testOBJ->questions) )
		{
			return true;
		}

		return false;
	}
	
	/**
	 * returns the fact wether a useable question set config exists or not
	 * 
	 * @return boolean
	 */
	public function doesQuestionSetRelatedDataExist()
	{
		return $this->isQuestionSetConfigured();
	}
	
	/**
	 * removes all question set config related data
	 */
	public function removeQuestionSetRelatedData()
	{
		$this->testOBJ->removeAllTestEditings();

		$this->db->manipulateF("DELETE FROM tst_test_question WHERE test_fi = %s",
			array('integer'),
			array($this->testOBJ->getTestId())
		);
		$this->testOBJ->questions = array();
		$this->testOBJ->saveCompleteStatus($this);
	}

	/**
	 * loads the question set config for current test from the database
	 */
	public function loadFromDb()
	{
		// TODO: Implement loadFromDb() method.
	}

	/**
	 * saves the question set config for current test to the database
	 */
	public function saveToDb()
	{
		// TODO: Implement saveToDb() method.
	}

	/**
	 * deletes the question set config for current test from the database
	 */
	public function deleteFromDb()
	{
		// TODO: Implement deleteFromDb() method.
	}
}
