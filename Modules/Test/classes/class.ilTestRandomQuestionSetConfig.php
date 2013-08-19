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
class ilTestRandomQuestionSetConfig extends ilTestQuestionSetConfig
{
	/**
	 * returns the fact wether a useable question set config exists or not
	 * 
	 * @return boolean
	 */
	public function isQuestionSetConfigured()
	{
		$randomQuestionPools = $this->testOBJ->getRandomQuestionpools();

		if( count($randomQuestionPools) && $this->testOBJ->getRandomQuestionCount() )
		{
			return true;
		}

		foreach( $randomQuestionPools as $poolData )
		{
			if( $poolData['count'] )
			{
				return true;
			}
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
		if( count($this->testOBJ->getRandomQuestionpools()) )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * removes all question set config related data
	 */
	public function removeQuestionSetRelatedData()
	{
		// delete eventually set random question pools of a previous random test
		$this->testOBJ->removeAllTestEditings();
		$this->db->manipulateF("DELETE FROM tst_test_random WHERE test_fi = %s",
			array('integer'),
			array($this->testOBJ->getTestId())
		);
		$this->testOBJ->questions = array();
		$this->testOBJ->saveCompleteStatus($this);
	}
}
