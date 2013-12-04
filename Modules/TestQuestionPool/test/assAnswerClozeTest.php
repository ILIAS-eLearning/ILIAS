<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assAnswerClozeTest extends PHPUnit_Framework_TestCase
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

	public function test_constructorShouldReturnInstance()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';

		// Act
		$instance = new assAnswerCloze();

		// Assert
		$this->assertNotNull($instance);
	}
	
	public function test_setGetLowerBound()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('2');
		
		// Act
		$expected = '1';
		$instance->setLowerBound($expected);
		$actual = $instance->getLowerBound();
		
		// Assert
		$this->assertEquals($expected, $actual);
	}
	
	public function test_setGetLowerBond_GreaterThanAnswerShouldSetAnswertext()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('2');

		// Act
		$expected = '2';
		$instance->setLowerBound(4);
		$actual = $instance->getLowerBound();

		// Assert
		$this->assertEquals($expected, $actual);		
	}
	
	public function test_setGetLowerBound_nonNumericShouldSetAnswertext()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('2');

		// Act
		$expected = '2';
		$instance->setLowerBound('test');
		$actual = $instance->getLowerBound();

		// Assert
		$this->assertEquals($expected, $actual);
	}
	
	public function test_setGetUpperBound()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('1');

		// Act
		$expected = '3';
		$instance->setUpperBound($expected);
		$actual = $instance->getUpperBound();

		// Assert
		$this->assertEquals($expected, $actual);		
	}
	
	public function test_setGetUpperBound_smallerThanAnswerShouldSetAnswertext()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('4');

		// Act
		$expected = '4';
		$instance->setUpperBound(2);
		$actual = $instance->getUpperBound();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetUpperBound_nonNumericShouldSetAnswertext()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$instance = new assAnswerCloze('4');

		// Act
		$expected = '4';
		$instance->setUpperBound('test');
		$actual = $instance->getUpperBound();

		// Assert
		$this->assertEquals($expected, $actual);
	}
}
