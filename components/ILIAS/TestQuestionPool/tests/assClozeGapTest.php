<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Refinery\Transformation;

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*/
class assClozeGapTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp(): void
    {
        chdir(__DIR__ . '../../../../');

        parent::setUp();

        $util_mock = $this->createMock('ilUtil', array('stripSlashes'), array(), '', false);
        $util_mock->expects($this->any())->method('stripSlashes')->will($this->returnArgument(0));
        $this->setGlobalVariable('ilUtils', $util_mock);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap

        $this->assertInstanceOf(assClozeGap::class, $instance);
    }

    public function test_setGetType_shouldReturnUnchangedValue(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $expected = 1; // 1 - select gap

        $instance->setType($expected);
        $actual = $instance->getType();

        $this->assertEquals($expected, $actual);
    }

    public function test_setType_shouldSetDefaultIfNotPassed(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $expected = 0; // 0 - text gap

        $instance->setType();
        $actual = $instance->getType();

        $this->assertEquals($expected, $actual);
    }

    public function test_setGetShuffle_shouldReturnUnchangedValue(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $expected = true;

        $instance->setShuffle($expected);
        $actual = $instance->getShuffle();

        $this->assertEquals($expected, $actual);
    }

    public function test_arrayShuffle_shouldNotReturnArrayUnshuffled(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap

        $the_unexpected = array('Killing', 'Kunkel', 'Luetzenkirchen',
            'Meyer', 'Jansen', 'Heyser', 'Becker');
        $instance->items = $the_unexpected;
        $instance->setShuffle(true);
        $theExpected = ['hua', 'haaa', 'some random values'];

        $transformationMock = $this->getMockBuilder(Transformation::class)->getMock();
        $transformationMock->expects(self::once())->method('transform')->with($the_unexpected)->willReturn($theExpected);
        $actual = $instance->getItems($transformationMock);

        $this->assertEquals($theExpected, $actual);
    }

    public function test_addGetItem_shouldReturnValueUnchanged(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $expected = new assAnswerCloze('Esther', 1.0, 0);

        $instance->addItem($expected);
        $actual = $instance->getItem(0);

        $this->assertEquals($expected, $actual);
    }

    public function test_addGetItem_shouldReturnValueUnchangedMultiple(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $answer = new assAnswerCloze('Bert', 1.0, 0);
        $expected = new assAnswerCloze('Esther', 1.0, 0);

        $instance->addItem($answer);
        $instance->addItem($expected);
        $actual = $instance->getItem(0);

        $this->assertEquals($expected, $actual);
    }

    public function test_getItem_shouldReturnNullIfNoItemAtGivenIndex(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $answer1 = new assAnswerCloze('Bert', 1.0, 0);
        $answer2 = new assAnswerCloze('Esther', 1.0, 1);

        $instance->addItem($answer1);
        $instance->addItem($answer2);

        $expected = null;

        $actual = $instance->getItem(2);

        $this->assertEquals($expected, $actual);
    }

    public function test_addGetItem_shouldReturnValueUnchangedMultiplePlus(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $answer = new assAnswerCloze('Bert', 1.0, 1);
        $answer2 = new assAnswerCloze('Fred', 1.0, 2);
        $answer3 = new assAnswerCloze('Karl', 1.0, 3);
        $expected = new assAnswerCloze('Esther', 1.0, 0);

        $instance->addItem($answer);
        $instance->addItem($answer2);
        $instance->addItem($answer3);
        $instance->addItem($expected);
        $actual = $instance->getItem(0);

        $this->assertEquals($expected, $actual);
    }

    public function test_getItems_shouldReturnItemsAdded(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze('Bert', 1.0, 0);
        $item2 = new assAnswerCloze('Fred', 1.0, 1);
        $item3 = new assAnswerCloze('Karl', 1.0, 2);
        $item4 = new assAnswerCloze('Esther', 1.0, 3);
        $instance->setShuffle(false);
        $expected = array($item1, $item2, $item3, $item4);
        $instance->addItem($item1);
        $instance->addItem($item2);
        $instance->addItem($item3);
        $instance->addItem($item4);
        $transformationMock = $this->getMockBuilder(Transformation::class)->getMock();
        $transformationMock->expects(self::never())->method('transform');
        $actual = $instance->getItems($transformationMock);

        $this->assertEquals($expected, $actual);
    }

    public function test_getItemsWithShuffle_shouldReturnItemsAddedShuffled(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $instance->setShuffle(true);
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

        foreach ($expected as $item) {
            $instance->addItem($item);
        }

        $transformationMock = $this->getMockBuilder(Transformation::class)->getMock();
        $transformationMock->expects(self::once())->method('transform')->with($expected)->willReturn($shuffledArray);
        $actual = $instance->getItems($transformationMock);

        $this->assertEquals($shuffledArray, $actual);
    }

    public function test_getItemsRaw_shouldReturnItemsAdded(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze('Bert', 1.0, 0);
        $item2 = new assAnswerCloze('Fred', 1.0, 1);
        $item3 = new assAnswerCloze('Karl', 1.0, 2);
        $item4 = new assAnswerCloze('Esther', 1.0, 3);
        $expected = array($item1, $item2, $item3, $item4);

        $instance->addItem($item1);
        $instance->addItem($item2);
        $instance->addItem($item3);
        $instance->addItem($item4);
        $actual = $instance->getItemsRaw();

        $this->assertEquals($expected, $actual);
    }

    public function test_getItemCount_shouldReturnCorrectCount(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze('Bert', 1.0, 0);
        $item2 = new assAnswerCloze('Fred', 1.0, 2);
        $item3 = new assAnswerCloze('Karl', 1.0, 1);
        $item4 = new assAnswerCloze('Esther', 1.0, 3);
        $expected = 4;

        $instance->addItem($item1);
        $instance->addItem($item2);
        $instance->addItem($item3);
        $instance->addItem($item4);
        $actual = $instance->getItemCount();

        $this->assertEquals($expected, $actual);
    }

    public function test_setItemPoints_shouldSetItemPoints(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze('Esther', 1.0, 0);
        $instance->addItem($item1);
        $expected = 4;

        $instance->setItemPoints(0, $expected);
        /** @var assAnswerCloze $item_retrieved */
        $item_retrieved = $instance->getItem(0);
        $actual = $item_retrieved->getPoints();

        $this->assertEquals($expected, $actual);
    }

    public function test_deleteItem_shouldDeleteGivenItem(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze('Bert', 1.0, 0);
        $item2 = new assAnswerCloze('Fred', 1.0, 1);

        $instance->addItem($item1);
        $instance->addItem($item2);

        $expected = array('1' => $item2);

        $instance->deleteItem(0);

        $actual = $instance->getItemsRaw();

        $this->assertEquals($expected, $actual);
    }

    public function test_clearItems_shouldClearItems(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze('Bert', 1.0, 0);
        $item2 = new assAnswerCloze('Fred', 1.0, 2);
        $item3 = new assAnswerCloze('Karl', 1.0, 1);
        $item4 = new assAnswerCloze('Esther', 1.0, 3);

        $instance->addItem($item1);
        $instance->addItem($item2);
        $instance->addItem($item3);
        $instance->addItem($item4);

        $expected = 0;

        $instance->clearItems();
        $actual = $instance->getItemCount();

        $this->assertEquals($expected, $actual);
    }

    public function test_setItemLowerBound_shouldSetItemsLowerBound(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze(20, 1.0, 0);

        $instance->addItem($item1);

        $expected = 10;

        $instance->setItemLowerBound(0, $expected);
        $item_retrieved = $instance->getItem(0);
        $actual = $item_retrieved->getLowerBound();

        $this->assertEquals($expected, $actual);
    }

    public function test_setItemLowerBound_shouldSetItemsAnswerIfBoundTooHigh(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze(20, 1.0, 0);

        $instance->addItem($item1);

        $expected = 40;

        $instance->setItemLowerBound(0, $expected);
        $item_retrieved = $instance->getItem(0);
        $actual = $item_retrieved->getLowerBound();

        $this->assertNotEquals($expected, $actual);
        $this->assertEquals($item_retrieved->getAnswerText(), $actual);
    }

    public function test_setItemUpperBound_shouldSetItemsUpperBound(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze(5, 1.0, 0);

        $instance->addItem($item1);

        $expected = 10;

        $instance->setItemUpperBound(0, $expected);
        $item_retrieved = $instance->getItem(0);
        $actual = $item_retrieved->getUpperBound();

        $this->assertEquals($expected, $actual);
    }

    public function test_setItemUpperBound_shouldSetItemsAnswerIfBoundTooLow(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze(20, 1.0, 0);

        $instance->addItem($item1);

        $expected = 10;

        $instance->setItemUpperBound(0, $expected);
        $item_retrieved = $instance->getItem(0);
        $actual = $item_retrieved->getUpperBound();

        $this->assertNotEquals($expected, $actual);
        $this->assertEquals($item_retrieved->getAnswerText(), $actual);
    }

    public function test_getMaxWidth_shouldReturnCharacterCountOfLongestAnswertext(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze('Bert', 1.0, 0);
        $item2 = new assAnswerCloze('Fred', 1.0, 2);
        $item3 = new assAnswerCloze('Karl', 1.0, 1);
        $item4 = new assAnswerCloze('Esther', 1.0, 3);

        $instance->addItem($item1);
        $instance->addItem($item2);
        $instance->addItem($item3);
        $instance->addItem($item4);

        $expected = strlen($item4->getAnswertext());

        $actual = $instance->getMaxWidth();

        $this->assertEquals($expected, $actual);
    }

    public function test_getBestSolutionIndexes_shouldReturnBestSolutionIndexes(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze('Bert', 1.0, 0);
        $item2 = new assAnswerCloze('Fred', 2.0, 2);
        $item3 = new assAnswerCloze('Karl', 3.0, 1);
        $item4 = new assAnswerCloze('Esther', 4.0, 3);

        $instance->addItem($item1);
        $instance->addItem($item2);
        $instance->addItem($item3);
        $instance->addItem($item4);

        $expected = array( 0 => 3 );

        $actual = $instance->getBestSolutionIndexes();

        $this->assertEquals($expected, $actual);
    }

    public function test_getBestSolutionOutput_shouldReturnBestSolutionOutput_CaseText(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze('Bert', 1.0, 0);
        $item2 = new assAnswerCloze('Fred', 2.0, 2);
        $item3 = new assAnswerCloze('Karl', 3.0, 1);
        $item4 = new assAnswerCloze('Esther', 4.0, 3);

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

        $actual = $instance->getBestSolutionOutput($this->getDummyTransformationMock());

        $this->assertEquals($expected, $actual);
    }

    public function test_getBestSolutionOutput_shouldReturnBestSolutionOutput_CaseTextMulti(): void
    {
        $instance = new assClozeGap(0); // 0 - text gap
        $item1 = new assAnswerCloze('Bert', 1.0, 0);
        $item2 = new assAnswerCloze('Fred', 2.0, 2);
        $item3 = new assAnswerCloze('Karl', 4, 1);
        $item4 = new assAnswerCloze('Esther', 4, 3);

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

        $actual = $instance->getBestSolutionOutput($this->getDummyTransformationMock());

        $this->assertTrue(($actual == $expected1) || ($actual == $expected2));
    }

    public function test_getBestSolutionOutput_shouldReturnBestSolutionOutput_CaseNumeric(): void
    {
        $instance = new assClozeGap(2); // 0 - text gap
        $item1 = new assAnswerCloze(10, 1.0, 0);
        $item2 = new assAnswerCloze(20, 2.0, 2);
        $item3 = new assAnswerCloze(30, 3.0, 1);
        $item4 = new assAnswerCloze(100, 4.0, 3);

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

        $actual = $instance->getBestSolutionOutput($this->getDummyTransformationMock());

        $this->assertEquals($expected, $actual);
    }

    public function test_getBestSolutionOutput_shouldReturnEmptyStringOnUnknownType_WhichMakesNoSenseButK(): void
    {
        $instance = new assClozeGap(11); // 0 - text gap
        $item1 = new assAnswerCloze(10, 1.0, 0);
        $item2 = new assAnswerCloze(20, 2.0, 2);
        $item3 = new assAnswerCloze(30, 3.0, 1);
        $item4 = new assAnswerCloze(100, 4.0, 3);

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

        $actual = $instance->getBestSolutionOutput($this->getDummyTransformationMock());

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
