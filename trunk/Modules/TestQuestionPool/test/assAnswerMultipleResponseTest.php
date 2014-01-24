<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMultipleResponseTest extends PHPUnit_Framework_TestCase
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

	public function test_instantiateObjectSimple()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMultipleResponse.php';

		// Act
		$instance = new ASS_AnswerMultipleResponse();

		// Assert
		$this->assertInstanceOf('ASS_AnswerMultipleResponse', $instance);
	}

	public function test_setGetPointsUnchecked()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMultipleResponse.php';
		$instance = new ASS_AnswerMultipleResponse();
		$expected = 1;

		// Act
		$instance->setPointsUnchecked($expected);
		$actual = $instance->getPointsUnchecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetPointsUnchecked_InvalidPointsBecomeZero()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMultipleResponse.php';
		$instance = new ASS_AnswerMultipleResponse();
		$expected = 0;

		// Act
		$instance->setPointsUnchecked('Günther');
		$actual = $instance->getPointsUnchecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetPointsChecked()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMultipleResponse.php';
		$instance = new ASS_AnswerMultipleResponse();
		$expected = 2;

		// Act
		$instance->setPointsChecked($expected);
		$actual = $instance->getPointsChecked();

		// Assert
		$this->assertEquals($expected, $actual);
	}
}
