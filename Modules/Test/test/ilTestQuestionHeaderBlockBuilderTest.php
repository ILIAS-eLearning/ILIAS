<?php

declare(strict_types=1);

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

        $this->testObj = new ilTestQuestionHeaderBlockBuilder(
            $this->createMock(ilLanguage::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestQuestionHeaderBlockBuilder::class, $this->testObj);
    }

    public function testHeaderMode(): void
    {
        $this->testObj->setHeaderMode(12);
        $this->assertEquals(12, $this->testObj->getHeaderMode());
    }

    public function testQuestionTitle(): void
    {
        $this->testObj->setQuestionTitle("test");
        $this->assertEquals("test", $this->testObj->getQuestionTitle());
    }

    public function testQuestionPoints(): void
    {
        $this->testObj->setQuestionPoints(20.5);
        $this->assertEquals(20.5, $this->testObj->getQuestionPoints());
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
        $this->testObj->setQuestionPosition(20);
        $this->assertEquals(20, $this->testObj->getQuestionPosition());
    }

    public function testQuestionCount(): void
    {
        $this->testObj->setQuestionCount(20);
        $this->assertEquals(20, $this->testObj->getQuestionCount());
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
        $this->testObj->setQuestionRelatedObjectives("test");
        $this->assertEquals("test", $this->testObj->getQuestionRelatedObjectives());
    }
}
