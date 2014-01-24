<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class ilAnswerWizardInputGUITest extends PHPUnit_Framework_TestCase
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
		// Arrange
		require_once './Services/Form/classes/class.ilTextInputGUI.php'; // I consider this a bug...
		require_once './Modules/TestQuestionPool/classes/class.ilAnswerWizardInputGUI.php';


		// Act
		$instance = new ilAnswerWizardInputGUI();

		$this->assertInstanceOf('ilAnswerWizardInputGUI', $instance);
	}
}
