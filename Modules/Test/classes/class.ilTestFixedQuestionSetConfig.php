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

		$res = $this->db->queryF(
			"SELECT question_fi FROM tst_test_question WHERE test_fi = %s",
			array('integer'), array($this->testOBJ->getTestId())
		);

		while( $row = $this->db->fetchAssoc($res) )
		{
			$this->testOBJ->removeQuestion($row["question_fi"]);
		}

		$this->db->manipulateF(
			"DELETE FROM tst_test_question WHERE test_fi = %s",
			array('integer'), array($this->testOBJ->getTestId())
		);

		$this->testOBJ->questions = array();

		$this->testOBJ->saveCompleteStatus($this);
	}

	public function resetQuestionSetRelatedTestSettings()
	{
		// nothing to do
	}

	/**
	 * removes all question set config related data for cloned/copied test
	 *
	 * @param ilObjTest $cloneTestOBJ
	 */
	public function cloneQuestionSetRelatedData($cloneTestOBJ)
	{
		global $ilLog;

		require_once 'Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
		require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';

		$cwo = ilCopyWizardOptions::_getInstance($cloneTestOBJ->getId());

		foreach( $this->testOBJ->questions as $key => $question_id )
		{
			$question = assQuestion::_instanciateQuestion($question_id);
			$cloneTestOBJ->questions[$key] = $question->duplicate(true, null, null, null, $cloneTestOBJ->getId());

			$original_id = assQuestion::_getOriginalId($question_id);

			$question = assQuestion::_instanciateQuestion($cloneTestOBJ->questions[$key]);
			$question->saveToDb($original_id);

			// Save the mapping of old question id <-> new question id
			// This will be used in class.ilObjCourse::cloneDependencies to copy learning objectives
			$originalKey = $this->testOBJ->getRefId().'_'.$question_id;
			$mappedKey = $cloneTestOBJ->getRefId().'_'.$cloneTestOBJ->questions[$key];
			$cwo->appendMapping($originalKey, $mappedKey);
			$ilLog->write(__METHOD__.": Added mapping $originalKey <-> $mappedKey");
		}
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
	 * saves the question set config for test with given id to the database
	 *
	 * @param $testId
	 */
	public function cloneToDbForTestId($testId)
	{
		// TODO: Implement saveToDbByTestId() method.
	}

	/**
	 * deletes the question set config for current test from the database
	 */
	public function deleteFromDb()
	{
		// TODO: Implement deleteFromDb() method.
	}

	public function isResultTaxonomyFilterSupported()
	{
		return false;
	}
}
