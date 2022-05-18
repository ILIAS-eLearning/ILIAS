<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . "/assBaseTestCase.php";

use ILIAS\Refinery\Transformation;

/**
* Unit tests
*
* @author Maximilian Becker <mbecker@databay.de>
* @author Marvin Beym <mbeym@databay.de>
*
* @ingroup ModulesTestQuestionPool
*/
class assClozeSelectGapTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    protected function setUp() : void
    {
        chdir(dirname(__FILE__));
        chdir('../../../');
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeSelectGap.php';

        // Act
        $instance = new assClozeSelectGap(1); // 1 - select gap

        $this->assertInstanceOf('assClozeSelectGap', $instance);
    }

    public function test_newlyInstatiatedObject_shouldReturnTrueOnGetShuffle() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeSelectGap.php';
        $instance = new assClozeSelectGap(1); // 1 - select gap
        $expected = true;

        $actual = $instance->getShuffle();

        $this->assertEquals($expected, $actual);
    }

    public function test_arrayShuffle_shouldShuffleArray() : void
    {
        // Arrange
        require_once './Modules/TestQuestionPool/classes/class.assClozeSelectGap.php';
        $instance = new assClozeSelectGap(1); // 1 - select gap
        $expected = ['shfksdfs', 'sfsdf', 'sdfsdfdf'];

        $transformationMock = $this->getMockBuilder(Transformation::class)->getMock();
        $transformationMock->expects(self::once())->method('transform')->willReturn($expected);
        $actual = $instance->getItems($transformationMock);
        $this->assertEquals($expected, $actual);
    }

    public function test_getItemswithShuffle_shouldReturnShuffledItems() : void
    {
        require_once './Modules/TestQuestionPool/classes/class.assClozeSelectGap.php';
        $instance = new assClozeSelectGap(1); // 1 - select gap

        require_once './Modules/TestQuestionPool/classes/class.assAnswerCloze.php';
        $item1 = new assAnswerCloze('Bert', 1.0, 0);
        $item2 = new assAnswerCloze('Fred', 2.0, 2);
        $item3 = new assAnswerCloze('Karl', 4, 1);
        $item4 = new assAnswerCloze('Esther', 4, 3);
        $item5 = new assAnswerCloze('Herbert', 1.0, 4);
        $item6 = new assAnswerCloze('Karina', 1.0, 5);
        $item7 = new assAnswerCloze('Helmut', 1.0, 6);
        $item8 = new assAnswerCloze('Kerstin', 1.0, 7);

        $instance->addItem($item1);
        $instance->addItem($item2);
        $instance->addItem($item3);
        $instance->addItem($item4);
        $instance->addItem($item5);
        $instance->addItem($item6);
        $instance->addItem($item7);
        $instance->addItem($item8);

        $instance->setType(true);

        $sequence = [$item1, $item3, $item2, $item4, $item5, $item6, $item7, $item8];
        $expectedSequence = array_reverse($sequence);

        $randomElmProvider = $this->getMockBuilder(Transformation::class)->getMock();
        $randomElmProvider->expects($this->once())
                          ->method('transform')
                          ->with($sequence)
                          ->willReturn($expectedSequence);

        $actual = $instance->getItems($randomElmProvider);
        $this->assertEquals($actual, $expectedSequence);
    }

    public function test_getItemswithoutShuffle_shouldReturnItemsInOrder() : void
    {
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
        $transformationMock = $this->getMockBuilder(Transformation::class)->getMock();
        $transformationMock->expects(self::once())->method('transform')->willReturnCallback(function ($value) {
            return $value;
        });
        $actual = $instance->getItems($transformationMock);

        $this->assertEquals($expected, $actual);
    }
}
