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

	public function test_arrayShuffle_shouldNotReturnArrayUnshuffled()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap

		// Act
		$the_unexpected = array('Killing', 'Kunkel', 'Luetzenkirchen',
			'Meyer', 'Jansen', 'Heyser', 'Becker');
		$actual = $instance->arrayShuffle($the_unexpected);

		// Assert
		$this->assertNotEquals($the_unexpected, $actual);
	}
	
	public function test_addGetItem_shouldReturnValueUnchanged()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$expected = new assAnswerCloze('Esther', 1.0, 0);
		
		// Act
		$instance->addItem($expected);
		$actual = $instance->getItem(0);
		
		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_addGetItem_shouldReturnValueUnchangedMultiple()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$answer = new assAnswerCloze('Bert', 1.0, 0);
		$expected = new assAnswerCloze('Esther', 1.0, 0);

		// Act
		$instance->addItem($answer);
		$instance->addItem($expected);
		$actual = $instance->getItem(0);

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_addGetItem_shouldReturnValueUnchangedMultiplePlus()
	{
		$this->markTestIncomplete('SUT defective. Please check the inappropriate use of array_push vs. order-indices.');
		// @TODO: Investigate addItem-Method.
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$answer = new assAnswerCloze('Bert', 1.0, 2);
		$answer2 = new assAnswerCloze('Fred', 1.0, 2);
		$answer3 = new assAnswerCloze('Karl', 1.0, 1);
		$expected = new assAnswerCloze('Esther', 1.0, 0);

		// Act
		$instance->addItem($answer);
		$instance->addItem($answer2);
		$instance->addItem($answer3);
		$instance->addItem($expected);
		$actual = $instance->getItem(0);

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_getItems_shouldReturnItemsAdded()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 1.0, 2);
		$item3 = new assAnswerCloze('Karl', 1.0, 1);
		$item4 = new assAnswerCloze('Esther', 1.0, 3);

		// Act
		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);
		$actual = $instance->getItems();

		// I have the feeling, that the order of the items in the return value is broken.
		// @TODO: Investigate addItem-Method.
		// Assert
		$this->assertTrue(is_array($actual));
		$this->assertTrue(in_array($item1, $actual));
		$this->assertTrue(in_array($item2, $actual));
		$this->assertTrue(in_array($item3, $actual));
		$this->assertTrue(in_array($item4, $actual));
	}

	public function test_getItemsWithShuffle_shouldReturnItemsAddedShuffled()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		$instance->setShuffle(true);
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 1.0, 2);
		$item3 = new assAnswerCloze('Karl', 1.0, 1);
		$item4 = new assAnswerCloze('Esther', 1.0, 3);

		// Act
		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);
		$actual = $instance->getItems();

		// I have the feeling, that the order of the items in the return value is broken.
		// @TODO: Investigate addItem-Method.
		// Assert
		$this->assertTrue(is_array($actual));
		$this->assertTrue(in_array($item1, $actual));
		$this->assertTrue(in_array($item2, $actual));
		$this->assertTrue(in_array($item3, $actual));
		$this->assertTrue(in_array($item4, $actual));
	}

	public function test_getItemsRaw_shouldReturnItemsAdded()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 1.0, 2);
		$item3 = new assAnswerCloze('Karl', 1.0, 1);
		$item4 = new assAnswerCloze('Esther', 1.0, 3);

		// Act
		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);
		$actual = $instance->getItemsRaw();

		// I have the feeling, that the order of the items in the return value is broken.
		// @TODO: Investigate addItem-Method.
		// Assert
		$this->assertTrue(is_array($actual));
		$this->assertTrue(in_array($item1, $actual));
		$this->assertTrue(in_array($item2, $actual));
		$this->assertTrue(in_array($item3, $actual));
		$this->assertTrue(in_array($item4, $actual));
	}

	public function test_getItemCount_shouldReturnCorrectCount()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 1.0, 2);
		$item3 = new assAnswerCloze('Karl', 1.0, 1);
		$item4 = new assAnswerCloze('Esther', 1.0, 3);
		$expected = 4;
		// Act
		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);
		$actual = $instance->getItemCount();

		// Assert
		$this->assertEquals($expected, $actual);
	}
}
