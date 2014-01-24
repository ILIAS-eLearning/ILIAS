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

	public function test_getItem_shouldReturnNullIfNoItemAtGivenIndex()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$answer1 = new assAnswerCloze('Bert', 1.0, 0);
		$answer2 = new assAnswerCloze('Esther', 1.0, 1);

		$instance->addItem($answer1);
		$instance->addItem($answer2);

		$expected = null;

		// Act
		$actual = $instance->getItem(2);

		// Assert
		$this->assertEquals($expected, $actual);
	}
	
	public function test_addGetItem_shouldReturnValueUnchangedMultiplePlus()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap

		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$answer = new assAnswerCloze('Bert', 1.0, 1);
		$answer2 = new assAnswerCloze('Fred', 1.0, 2);
		$answer3 = new assAnswerCloze('Karl', 1.0, 3);
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
		$item2 = new assAnswerCloze('Fred', 1.0, 1);
		$item3 = new assAnswerCloze('Karl', 1.0, 2);
		$item4 = new assAnswerCloze('Esther', 1.0, 3);
		$instance->setShuffle(false);
		$expected = array($item1, $item2, $item3, $item4);

		// Act
		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);
		$actual = $instance->getItems();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_getItemsWithShuffle_shouldReturnItemsAddedShuffled()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		$instance->setShuffle(true);
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 1.0, 1);
		$item3 = new assAnswerCloze('Karl', 1.0, 2);
		$item4 = new assAnswerCloze('Esther', 1.0, 3);
		$expected = array($item1, $item2, $item3, $item4);

		// Act
		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);
		$actual = $instance->getItems();

		// Assert
		$this->assertTrue(is_array($actual));
		$this->assertTrue(in_array($item1, $actual));
		$this->assertTrue(in_array($item2, $actual));
		$this->assertTrue(in_array($item3, $actual));
		$this->assertTrue(in_array($item4, $actual));
		$this->assertNotEquals($expected, $actual);
	}

	public function test_getItemsRaw_shouldReturnItemsAdded()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 1.0, 1);
		$item3 = new assAnswerCloze('Karl', 1.0, 2);
		$item4 = new assAnswerCloze('Esther', 1.0, 3);
		$expected = array($item1, $item2, $item3, $item4);

		// Act
		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);
		$actual = $instance->getItemsRaw();

		// Assert
		$this->assertEquals($expected, $actual);
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

	public function test_setItemPoints_shouldSetItemPoints()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Esther', 1.0, 0);
		$instance->addItem($item1);
		$expected = 4;

		// Act
		$instance->setItemPoints(0, $expected);
		/** @var assAnswerCloze $item_retrieved */
		$item_retrieved = $instance->getItem(0);
		$actual = $item_retrieved->getPoints();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_deleteItem_shouldDeleteGivenItem()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 1.0, 1);

		$instance->addItem($item1);
		$instance->addItem($item2);

		$expected = array('1' => $item2);

		// Act
		$instance->deleteItem(0);

		$actual = $instance->getItemsRaw();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_clearItems_shouldClearItems()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 1.0, 2);
		$item3 = new assAnswerCloze('Karl', 1.0, 1);
		$item4 = new assAnswerCloze('Esther', 1.0, 3);

		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);

		$expected = 0;

		// Act
		$instance->clearItems();
		$actual = $instance->getItemCount();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setItemLowerBound_shouldSetItemsLowerBound()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze(20, 1.0, 0);

		$instance->addItem($item1);

		$expected = 10;

		// Act
		$instance->setItemLowerBound(0, $expected);
		$item_retrieved = $instance->getItem(0);
		$actual = $item_retrieved->getLowerBound();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setItemLowerBound_shouldSetItemsAnswerIfBoundTooHigh()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze(20, 1.0, 0);

		$instance->addItem($item1);

		$expected = 40;

		// Act
		$instance->setItemLowerBound(0, $expected);
		$item_retrieved = $instance->getItem(0);
		$actual = $item_retrieved->getLowerBound();

		// Assert
		$this->assertNotEquals($expected, $actual);
		$this->assertEquals($item_retrieved->getAnswerText(), $actual);
	}

	public function test_setItemUpperBound_shouldSetItemsUpperBound()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze(5, 1.0, 0);

		$instance->addItem($item1);

		$expected = 10;

		// Act
		$instance->setItemUpperBound(0, $expected);
		$item_retrieved = $instance->getItem(0);
		$actual = $item_retrieved->getUpperBound();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_setItemUpperBound_shouldSetItemsAnswerIfBoundTooLow()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze(20, 1.0, 0);

		$instance->addItem($item1);

		$expected = 10;

		// Act
		$instance->setItemUpperBound(0, $expected);
		$item_retrieved = $instance->getItem(0);
		$actual = $item_retrieved->getUpperBound();

		// Assert
		$this->assertNotEquals($expected, $actual);
		$this->assertEquals($item_retrieved->getAnswerText(), $actual);
	}

	public function test_getMaxWidth_shouldReturnCharacterCountOfLongestAnswertext()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 1.0, 2);
		$item3 = new assAnswerCloze('Karl', 1.0, 1);
		$item4 = new assAnswerCloze('Esther', 1.0, 3);

		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);

		$expected = strlen($item4->getAnswertext());

		// Act
		$actual = $instance->getMaxWidth();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_getBestSolutionIndexes_shouldReturnBestSolutionIndexes()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 2.0, 2);
		$item3 = new assAnswerCloze('Karl', 3.0, 1);
		$item4 = new assAnswerCloze('Esther', 4.0, 3);

		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);

		$expected = array( 0 => 3 );

		// Act
		$actual = $instance->getBestSolutionIndexes();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_getBestSolutionOutput_shouldReturnBestSolutionOutput_CaseText()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 2.0, 2);
		$item3 = new assAnswerCloze('Karl', 3.0, 1);
		$item4 = new assAnswerCloze('Esther', 4.0, 3);

		// We need the $lng-mock.
		require_once './Services/Language/classes/class.ilLanguage.php';
		$lng_mock = $this->getMock('ilLanguage', array('txt'), array(), '', false);
		$lng_mock->expects( $this->any() )->method( 'txt' )->will( $this->returnValue('Test') );
		global $lng;
		$lng = $lng_mock;

		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);

		$expected = 'Esther';

		// Act
		$actual = $instance->getBestSolutionOutput();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_getBestSolutionOutput_shouldReturnBestSolutionOutput_CaseTextMulti()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(0); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 2.0, 2);
		$item3 = new assAnswerCloze('Karl', 4, 1);
		$item4 = new assAnswerCloze('Esther', 4, 3);

		// We need the $lng-mock.
		require_once './Services/Language/classes/class.ilLanguage.php';
		$lng_mock = $this->getMock('ilLanguage', array('txt'), array(), '', false);
		$lng_mock->expects( $this->any() )->method( 'txt' )->will( $this->returnValue('or') );
		global $lng;
		$lng = $lng_mock;

		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);

		$expected1 = 'Karl or Esther';
		$expected2 = 'Esther or Karl';

		// Act
		$actual = $instance->getBestSolutionOutput();

		// Assert
		$this->assertTrue( ($actual == $expected1) || ($actual == $expected2) );
	}

	public function test_getBestSolutionOutput_shouldReturnBestSolutionOutput_CaseNumeric()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(2); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze(10, 1.0, 0);
		$item2 = new assAnswerCloze(20, 2.0, 2);
		$item3 = new assAnswerCloze(30, 3.0, 1);
		$item4 = new assAnswerCloze(100, 4.0, 3);

		// We need the $lng-mock.
		require_once './Services/Language/classes/class.ilLanguage.php';
		$lng_mock = $this->getMock('ilLanguage', array('txt'), array(), '', false);
		$lng_mock->expects( $this->any() )->method( 'txt' )->will( $this->returnValue('Test') );
		global $lng;
		$lng = $lng_mock;

		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);

		$expected = 100;

		// Act
		$actual = $instance->getBestSolutionOutput();

		// Assert
		$this->assertEquals($expected, $actual);
	}

	public function test_getBestSolutionOutput_shouldReturnEmptyStringOnUnknownType_WhichMakesNoSenseButK()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
		$instance = new assClozeGap(11); // 0 - text gap
		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze(10, 1.0, 0);
		$item2 = new assAnswerCloze(20, 2.0, 2);
		$item3 = new assAnswerCloze(30, 3.0, 1);
		$item4 = new assAnswerCloze(100, 4.0, 3);

		// We need the $lng-mock.
		require_once './Services/Language/classes/class.ilLanguage.php';
		$lng_mock = $this->getMock('ilLanguage', array('txt'), array(), '', false);
		$lng_mock->expects( $this->any() )->method( 'txt' )->will( $this->returnValue('Test') );
		global $lng;
		$lng = $lng_mock;

		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);

		$expected = '';

		// Act
		$actual = $instance->getBestSolutionOutput();

		// Assert
		$this->assertEquals($expected, $actual);
	}
}
