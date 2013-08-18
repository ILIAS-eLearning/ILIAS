<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerBinaryStateTest extends PHPUnit_Framework_TestCase
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
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';

		// Act
		$instance = new ASS_AnswerBinaryState();

		$this->assertInstanceOf('ASS_AnswerBinaryState', $instance);
	}

	public function test_setGetState_shouldReturnUnchangedState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 1;

		// Act
		$instance->setState($expected);
		$actual = $instance->getState();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_isStateChecked_shouldReturnActualState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 1;

		// Act
		$instance->setState($expected);
		$actual = $instance->isStateChecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_isStateSet_shouldReturnActualState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 1;

		// Act
		$instance->setState($expected);
		$actual = $instance->isStateSet();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_isStateUnset_shouldReturnActualState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 1;

		// Act
		$instance->setState($expected);
		$actual = !$instance->isStateUnset();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_isStateUnchecked_shouldReturnActualState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 1;

		// Act
		$instance->setState($expected);
		$actual = !$instance->isStateUnchecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setChecked_shouldAlterState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 0;
		$instance->setState($expected);

		// Act
		$instance->setChecked();
		$actual = $instance->isStateUnchecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setUnchecked_shouldAlterState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 1;
		$instance->setState($expected);

		// Act
		$instance->setUnchecked();
		$actual = $instance->isStateUnchecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setSet_shouldAlterState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 0;
		$instance->setState($expected);

		// Act
		$instance->setSet();
		$actual = $instance->isStateUnchecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setUnset_shouldAlterState()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerBinaryState.php';
		$instance = new ASS_AnswerBinaryState();
		$expected = 1;
		$instance->setState($expected);

		// Act
		$instance->setUnset();
		$actual = $instance->isStateUnchecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}
}