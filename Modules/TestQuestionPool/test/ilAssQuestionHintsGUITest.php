<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class ilAssQuestionHintsGUITest extends PHPUnit_Framework_TestCase
{
	protected $backupGlobals = FALSE;

	protected function setUp()
	{
		if (defined('ILIAS_PHPUNIT_CONTEXT'))
		{
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			ilUnitUtil::performInitialisation();
		}
		else
		{
			chdir( dirname( __FILE__ ) );
			chdir('../../../');
		}
	}

	public function test_instantiateObject_shouldReturnInstance()
	{
		$this->markTestIncomplete('Needs mock.');
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.ilAssQuestionHintsGUI.php';

		// Act
		$instance = new ilAssQuestionHintsGUI();

		$this->assertInstanceOf('ASS_AnswerBinaryStateImage', $instance);
	}
}
