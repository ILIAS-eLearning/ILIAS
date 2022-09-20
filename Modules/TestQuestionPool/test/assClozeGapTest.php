<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Transformation;

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assClozeGapTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');

        parent::setUp();

        require_once './Services/Utilities/classes/class.ilUtil.php';
        $util_mock = $this->createMock('ilUtil', array('stripSlashes'), array(), '', false);
        $util_mock->expects($this->any())->method('stripSlashes')->will($this->returnArgument(0));
        $this->setGlobalVariable('ilUtils', $util_mock);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';

        // Act
        $instance = new assClozeGap(0); // 0 - text gap

        $this->assertInstanceOf('assClozeGap', $instance);
    }

    public function test_setGetType_shouldReturnUnchangedValue(): void
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

    public function test_setType_shouldSetDefaultIfNotPassed(): void
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

    public function test_setGetShuffle_shouldReturnUnchangedValue(): void
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

    public function test_arrayShuffle_shouldNotReturnArrayUnshuffled(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';

        $instance = new assClozeGap(0); // 0 - text gap

        // Act
        $the_unexpected = array('Killing', 'Kunkel', 'Luetzenkirchen',
            'Meyer', 'Jansen', 'Heyser', 'Becker');
        $instance->items = $the_unexpected;
        $instance->setShuffle(true);
        $theExpected = ['hua', 'haaa', 'some random values'];

        $transformationMock = $this->getMockBuilder(Transformation::class)->getMock();
        $transformationMock->expects(self::once())->method('transform')->with($the_unexpected)->willReturn($theExpected);
        $actual = $instance->getItems($transformationMock);

        // Assert
        $this->assertEquals($theExpected, $actual);
    }

    public function test_addGetItem_shouldReturnValueUnchanged(): void
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

    public function test_addGetItem_shouldReturnValueUnchangedMultiple(): void
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

    public function test_getItem_shouldReturnNullIfNoItemAtGivenIndex(): void
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

    public function test_addGetItem_shouldReturnValueUnchangedMultiplePlus(): void
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

    public function test_getItems_shouldReturnItemsAdded(): void
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
        $transformationMock = $this->getMockBuilder(Transformation::class)->getMock();
        $transformationMock->expects(self::never())->method('transform');
        $actual = $instance->getItems($transformationMock);

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_getItemsWithShuffle_shouldReturnItemsAddedShuffled(): void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeGap.php';
        $instance = new assClozeGap(0); // 0 - text gap
        $instance->setShuffle(true);
        require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
        $expected = [
            new assAnswerCloze('Bert', 1.0, 0),
            new assAnswerCloze('Fred', 1.0, 1),
            new assAnswerCloze('Karl', 1.0, 2),
            new assAnswerCloze('Esther', 1.0, 3),
            new assAnswerCloze('Herbert', 1.0, 4),
            new assAnswerCloze('Karina', 1.0, 5),
            new assAnswerCloze('Helmut', 1.0, 6),
            new assAnswerCloze('Kerstin', 1.0, 7),
        ];

        $shuffledArray = ['some shuffled array', 'these values dont matter'];

        // Act
        foreach ($expected as $item) {
            $instance->addItem($item);
        }

        $transformationMock = $this->getMockBuilder(Transformation::class)->getMock();
        $transformationMock->expects(self::once())->method('transform')->with($expected)->willReturn($shuffledArray);
        $actual = $instance->getItems($transformationMock);

        // Assert

        $this->assertEquals($shuffledArray, $actual);
    }

    public function test_getItemsRaw_shouldReturnItemsAdded(): void
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

    public function test_getItemCount_shouldReturnCorrectCount(): void
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

    public function test_setItemPoints_shouldSetItemPoints(): void
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

    public function test_deleteItem_shouldDeleteGivenItem(): void
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

    public function test_clearItems_shouldClearItems(): void
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

    public function test_setItemLowerBound_shouldSetItemsLowerBound(): void
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

    public function test_setItemLowerBound_shouldSetItemsAnswerIfBoundTooHigh(): void
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

    public function test_setItemUpperBound_shouldSetItemsUpperBound(): void
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

    public function test_setItemUpperBound_shouldSetItemsAnswerIfBoundTooLow(): void
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

    public function test_getMaxWidth_shouldReturnCharacterCountOfLongestAnswertext(): void
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

    public function test_getBestSolutionIndexes_shouldReturnBestSolutionIndexes(): void
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

    public function test_getBestSolutionOutput_shouldReturnBestSolutionOutput_CaseText(): void
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
        $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
        $lng_mock->expects($this->any())->method('txt')->will($this->returnValue('Test'));
        global $DIC;
        unset($DIC['lng']);
        $DIC['lng'] = $lng_mock;
        $GLOBALS['lng'] = $DIC['lng'];

        $instance->addItem($item1);
        $instance->addItem($item2);
        $instance->addItem($item3);
        $instance->addItem($item4);

        $expected = 'Esther';

        // Act
        $actual = $instance->getBestSolutionOutput($this->getDummyTransformationMock());

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_getBestSolutionOutput_shouldReturnBestSolutionOutput_CaseTextMulti(): void
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
        $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
        $lng_mock->expects($this->any())->method('txt')->will($this->returnValue('or'));
        global $DIC;
        unset($DIC['lng']);
        $DIC['lng'] = $lng_mock;
        $GLOBALS['lng'] = $DIC['lng'];

        $instance->setShuffle(true);
        $instance->addItem($item1);
        $instance->addItem($item2);
        $instance->addItem($item3);
        $instance->addItem($item4);

        $expected1 = 'Karl or Esther';
        $expected2 = 'Esther or Karl';

        // Act
        $actual = $instance->getBestSolutionOutput($this->getDummyTransformationMock());

        // Assert
        $this->assertTrue(($actual == $expected1) || ($actual == $expected2));
    }

    public function test_getBestSolutionOutput_shouldReturnBestSolutionOutput_CaseNumeric(): void
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
        $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
        $lng_mock->expects($this->any())->method('txt')->will($this->returnValue('Test'));
        global $DIC;
        unset($DIC['lng']);
        $DIC['lng'] = $lng_mock;
        $GLOBALS['lng'] = $DIC['lng'];

        $instance->addItem($item1);
        $instance->addItem($item2);
        $instance->addItem($item3);
        $instance->addItem($item4);

        $expected = 100;

        // Act
        $actual = $instance->getBestSolutionOutput($this->getDummyTransformationMock());

        // Assert
        $this->assertEquals($expected, $actual);
    }

    public function test_getBestSolutionOutput_shouldReturnEmptyStringOnUnknownType_WhichMakesNoSenseButK(): void
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
        $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
        $lng_mock->expects($this->any())->method('txt')->will($this->returnValue('Test'));
        global $DIC;
        unset($DIC['lng']);
        $DIC['lng'] = $lng_mock;
        $GLOBALS['lng'] = $DIC['lng'];

        $instance->addItem($item1);
        $instance->addItem($item2);
        $instance->addItem($item3);
        $instance->addItem($item4);

        $expected = '';

        // Act
        $actual = $instance->getBestSolutionOutput($this->getDummyTransformationMock());

        // Assert
        $this->assertEquals($expected, $actual);
    }

    private function getDummyTransformationMock(): Transformation
    {
        $transformationMock = $this->getMockBuilder(Transformation::class)->getMock();
        $transformationMock->expects(self::any())->method('transform')->willReturnCallback(static function (array $array) {
            return $array;
        });

        return $transformationMock;
    }
}
