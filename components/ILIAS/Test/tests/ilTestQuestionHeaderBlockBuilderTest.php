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

declare(strict_types=1);

/**
 * Class ilTestQuestionHeaderBlockBuilderTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestQuestionHeaderBlockBuilderTest extends ilTestBaseTestCase
{
    private ilTestQuestionHeaderBlockBuilder $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestQuestionHeaderBlockBuilder($this->createMock(ilLanguage::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionHeaderBlockBuilder::class, $this->testObj);
    }

    public function testHeaderMode(): void
    {
        $headerMode = 12;
        $this->testObj->setHeaderMode($headerMode);
        $this->assertEquals($headerMode, $this->testObj->getHeaderMode());
    }

    public function testQuestionTitle(): void
    {
        $questionTitle = 'test';
        $this->testObj->setQuestionTitle($questionTitle);
        $this->assertEquals($questionTitle, $this->testObj->getQuestionTitle());
    }

    public function testQuestionPoints(): void
    {
        $questionPoints = 20.5;
        $this->testObj->setQuestionPoints($questionPoints);
        $this->assertEquals($questionPoints, $this->testObj->getQuestionPoints());
    }

    public function testQuestionAnswered(): void
    {
        $this->testObj->setQuestionAnswered(false);
        $this->assertFalse($this->testObj->isQuestionAnswered());

        $this->testObj->setQuestionAnswered(true);
        $this->assertTrue($this->testObj->isQuestionAnswered());
    }

    public function testQuestionPosition(): void
    {
        $questionPosition = 20;
        $this->testObj->setQuestionPosition($questionPosition);
        $this->assertEquals($questionPosition, $this->testObj->getQuestionPosition());
    }

    public function testQuestionCount(): void
    {
        $questionCount = 20;
        $this->testObj->setQuestionCount($questionCount);
        $this->assertEquals($questionCount, $this->testObj->getQuestionCount());
    }

    public function testQuestionPostponed(): void
    {
        $this->testObj->setQuestionPostponed(false);
        $this->assertFalse($this->testObj->isQuestionPostponed());

        $this->testObj->setQuestionPostponed(true);
        $this->assertTrue($this->testObj->isQuestionPostponed());
    }

    public function testQuestionObligatory(): void
    {
        $this->testObj->setQuestionObligatory(false);
        $this->assertFalse($this->testObj->isQuestionObligatory());

        $this->testObj->setQuestionObligatory(true);
        $this->assertTrue($this->testObj->isQuestionObligatory());
    }

    public function testQuestionRelatedObjectives(): void
    {
        $questionRelatedObjectives = 'test';
        $this->testObj->setQuestionRelatedObjectives($questionRelatedObjectives);
        $this->assertEquals($questionRelatedObjectives, $this->testObj->getQuestionRelatedObjectives());
    }
}
