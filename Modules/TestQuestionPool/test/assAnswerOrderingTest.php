<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/Database/interfaces/interface.ilDBInterface.php';
/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerOrderingTest extends PHPUnit_Framework_TestCase
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
		require_once './Modules/TestQuestionPool/classes/class.assAnswerOrdering.php';

		// Act
		$instance = new ASS_AnswerOrdering();

		$this->assertInstanceOf('ASS_AnswerOrdering', $instance);
	}

	public function test_setGetRandomId()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerOrdering.php';
		$instance = new ASS_AnswerOrdering();
		$expected = 13579;

		// Act
		$instance->setRandomID($expected);
		$actual = $instance->getRandomID();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetAnswerId()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerOrdering.php';
		$instance = new ASS_AnswerOrdering();
		$expected = 13579;

		// Act
		$instance->setAnswerId($expected);
		$actual = $instance->getAnswerId();

		// Assert
		$this->assertEquals($expected, $actual);
	}


	public function test_setGetOrdeingDepth()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerOrdering.php';
		$instance = new ASS_AnswerOrdering();
		$expected = 13579;

		// Act
		$instance->setSolutionIndentation($expected);
		$actual = $instance->getSolutionIndentation();

		// Assert
		$this->assertEquals($expected, $actual);
	}
}
