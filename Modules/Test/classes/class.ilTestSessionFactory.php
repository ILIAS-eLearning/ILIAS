<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for test session
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 * 
 * @package		Modules/Test
 */
class ilTestSessionFactory
{
	/**
	 * singleton instance of test session
	 *
	 * @var ilTestSession|ilTestSessionDynamicQuestionSet
	 */
	private static $testSession = null;
	
	/**
	 * object instance of current test
	 *
	 * @var ilObjTest
	 */
	private $testOBJ = null;
	
	/**
	 * constructor
	 * 
	 * @param ilObjTest $testOBJ
	 */
	public function __construct(ilObjTest $testOBJ)
	{
		$this->testOBJ = $testOBJ;
	}
	
	/**
	 * creates and returns an instance of a test sequence
	 * that corresponds to the current test mode
	 * 
	 * @param integer $activeId
	 * @return ilTestSession|ilTestSessionDynamicQuestionSet
	 */
	public function getSession($activeId = null)
	{
		global $ilUser;
		
		if(self::$testSession === null)
		{
			switch( $this->testOBJ->getQuestionSetType() )
			{
				case ilObjTest::QUESTION_SET_TYPE_FIXED:
				case ilObjTest::QUESTION_SET_TYPE_RANDOM:

					global $ilUser;
					
					require_once 'Modules/Test/classes/class.ilTestSession.php';
					self::$testSession = new ilTestSession();
					break;

				case ilObjTest::QUESTION_SET_TYPE_DYNAMIC:

					require_once 'Modules/Test/classes/class.ilTestSessionDynamicQuestionSet.php';
					self::$testSession = new ilTestSessionDynamicQuestionSet();
					break;
			}
			
			self::$testSession->setRefId($this->testOBJ->getRefId());
			self::$testSession->setTestId($this->testOBJ->getTestId());
			if($activeId)
			{
				self::$testSession->loadFromDb($activeId);
			}
			else
			{
				self::$testSession->loadTestSession(
					$this->testOBJ->getTestId(), $ilUser->getId(), $_SESSION["tst_access_code"][$this->testOBJ->getTestId()]
				);
			}
		}

		return self::$testSession;
	}
}
