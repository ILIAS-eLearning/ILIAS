<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/** 
* Unit tests
* 
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assClozeSelectGapTest extends PHPUnit_Framework_TestCase
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
		require_once './Modules/TestQuestionPool/classes/class.assClozeSelectGap.php';

		// Act
		$instance = new assClozeSelectGap(1); // 1 - select gap

		$this->assertInstanceOf('assClozeSelectGap', $instance);
	}

	public function test_newlyInstatiatedObject_shouldReturnTrueOnGetShuffle()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeSelectGap.php';
		$instance = new assClozeSelectGap(1); // 1 - select gap
		$expected = true;

		$actual = $instance->getShuffle();

		$this->assertEquals($expected, $actual);
	}

	public function test_setType_shouldSetShuffling()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeSelectGap.php';
		$instance = new assClozeSelectGap(1); // 1 - select gap
		$expected = false;

		$instance->setType($expected);
		$actual = $instance->getShuffle();

		$this->assertEquals($expected, $actual);
	}

	public function test_arrayShuffle_shouldShuffleArray()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeSelectGap.php';
		$instance = new assClozeSelectGap(1); // 1 - select gap
		$expected = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20);
		
		$actual = $instance->arrayShuffle($expected);

		$this->assertNotEquals($expected, $actual);
	}

	public function test_getItemswithShuffle_shouldReturnShuffledItems()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeSelectGap.php';
		$instance = new assClozeSelectGap(1); // 1 - select gap

		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 2.0, 2);
		$item3 = new assAnswerCloze('Karl', 4, 1);
		$item4 = new assAnswerCloze('Esther', 4, 3);

		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);

		$instance->setType(true);

		$expected = array($item1, $item2, $item3, $item4);

		$actual = $instance->getItems();

		$this->assertNotEquals($expected, $actual);
	}

	public function test_getItemswithoutShuffle_shouldReturnItemsInOrder()
	{
		// Arrange
		require_once './Modules/TestQuestionPool/classes/class.assClozeSelectGap.php';
		$instance = new assClozeSelectGap(1); // 1 - select gap

		require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
		$item1 = new assAnswerCloze('Bert', 1.0, 0);
		$item2 = new assAnswerCloze('Fred', 2.0, 1);
		$item3 = new assAnswerCloze('Karl', 4, 2);
		$item4 = new assAnswerCloze('Esther', 4, 3);

		$instance->addItem($item1);
		$instance->addItem($item2);
		$instance->addItem($item3);
		$instance->addItem($item4);

		$instance->setType(false);

		$expected = array($item1, $item2, $item3, $item4);

		$actual = $instance->getItems();

		$this->assertEquals($expected, $actual);
	}
}
