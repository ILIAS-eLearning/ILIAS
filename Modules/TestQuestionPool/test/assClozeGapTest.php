<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assClozeGapTest extends PHPUnit_Framework_TestCase
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
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';

		// Act
		$instance = new assClozeGap(0); // 0 - text gap

		$this->assertInstanceOf('assClozeGap', $instance);
	}

	public function test_setGetType_shouldReturnUnchangedValue()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		$expected = 1; // 1 - select gap

		// Act
		$instance->setType($expected);
		$actual = $instance->getType();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setType_shouldSetDefaultIfNotPassed()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		$expected = 0; // 0 - text gap

		// Act
		$instance->setType();
		$actual = $instance->getType();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setGetShuffle_shouldReturnUnchangedValue()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		$expected = true;

		// Act
		$instance->setShuffle($expected);
		$actual = $instance->getShuffle();

		// Assert
		$this->assertEquals($expected, $actual);
	}
}
