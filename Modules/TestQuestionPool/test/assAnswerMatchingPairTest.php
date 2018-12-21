<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerMatchingPairTest extends PHPUnit_Framework_TestCase
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
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';

		// Act
		$instance = new assAnswerMatchingPair();

		// Assert
		$this->assertInstanceOf('assAnswerMatchingPair', $instance);
	}

	public function test_setGetTerm()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';
		$instance = new assAnswerMatchingPair();
		$expected = 'Term';

		// Act
		$instance->term = $expected;
		$actual = $instance->term;

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetDefinition()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';
		$instance = new assAnswerMatchingPair();
		$expected = 'Definition';

		// Act
		$instance->definition = $expected;
		$actual = $instance->definition;

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetPoints()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';
		$instance = new assAnswerMatchingPair();
		$expected = 'Definition';

		// Act
		$instance->points = $expected;
		$actual = $instance->points;

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetHokum()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerMatchingPair.php';
		$instance = new assAnswerMatchingPair();
		$expected = null;

		// Act
		$instance->hokum = 'Hokum Value';
		$actual = $instance->hokum;

		// Assert
		$this->assertEquals($expected, $actual);
	}


}
