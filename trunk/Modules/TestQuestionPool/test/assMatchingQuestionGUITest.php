<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assMatchingQuestionGUITest extends PHPUnit_Framework_TestCase
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

			require_once './Services/UICore/classes/class.ilCtrl.php';
			$ilCtrl_mock = $this->getMock('ilCtrl');
			$ilCtrl_mock->expects( $this->any() )->method( 'saveParameter' );
			$ilCtrl_mock->expects( $this->any() )->method( 'saveParameterByClass' );
			global $ilCtrl;
			$ilCtrl = $ilCtrl_mock;

			require_once './Services/Language/classes/class.ilLanguage.php';
			$lng_mock = $this->getMock('ilLanguage', array('txt'), array(), '', false);
			//$lng_mock->expects( $this->once() )->method( 'txt' )->will( $this->returnValue('Test') );
			global $lng;
			$lng = $lng_mock;

			$ilias_mock = new stdClass();
			$ilias_mock->account = new stdClass();
			$ilias_mock->account->id = 6;
			$ilias_mock->account->fullname = 'Esther Tester';
			global $ilias;
			$ilias = $ilias_mock;
		}
	}

	public function test_instantiateObject_shouldReturnInstance()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assMatchingQuestionGUI.php';

		// Act
		$instance = new assMatchingQuestionGUI();

		$this->assertInstanceOf('assMatchingQuestionGUI', $instance);
	}
}
