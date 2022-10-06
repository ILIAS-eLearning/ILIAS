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
 * Class ilTestDynamicQuestionSetFilterSelectionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestDynamicQuestionSetFilterSelectionTest extends ilTestBaseTestCase
{
    private ilTestDynamicQuestionSetFilterSelection $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestDynamicQuestionSetFilterSelection();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestDynamicQuestionSetFilterSelection::class, $this->testObj);
    }

    public function testAnswerStatusActiveId(): void
    {
        $this->testObj->setAnswerStatusActiveId(1250);
        $this->assertEquals(1250, $this->testObj->getAnswerStatusActiveId());
    }

    public function testAnswerStatusSelection(): void
    {
        $this->testObj->setAnswerStatusSelection("testString");
        $this->assertEquals("testString", $this->testObj->getAnswerStatusSelection());
    }

    public function testHasAnswerStatusSelection(): void
    {
        $this->testObj->setAnswerStatusSelection(
            ilTestDynamicQuestionSetFilterSelection::ANSWER_STATUS_FILTER_VALUE_ALL_NON_CORRECT
        );
        $this->assertTrue($this->testObj->hasAnswerStatusSelection());

        $this->testObj->setAnswerStatusSelection(
            ilTestDynamicQuestionSetFilterSelection::ANSWER_STATUS_FILTER_VALUE_NON_ANSWERED
        );
        $this->assertTrue($this->testObj->hasAnswerStatusSelection());

        $this->testObj->setAnswerStatusSelection(
            ilTestDynamicQuestionSetFilterSelection::ANSWER_STATUS_FILTER_VALUE_WRONG_ANSWERED
        );
        $this->assertTrue($this->testObj->hasAnswerStatusSelection());

        $this->testObj->setAnswerStatusSelection("testString");
        $this->assertFalse($this->testObj->hasAnswerStatusSelection());
    }

    public function testIsAnswerStatusSelectionWrongAnswered(): void
    {
        $this->testObj->setAnswerStatusSelection(
            ilTestDynamicQuestionSetFilterSelection::ANSWER_STATUS_FILTER_VALUE_ALL_NON_CORRECT
        );
        $this->assertFalse($this->testObj->isAnswerStatusSelectionWrongAnswered());

        $this->testObj->setAnswerStatusSelection(
            ilTestDynamicQuestionSetFilterSelection::ANSWER_STATUS_FILTER_VALUE_NON_ANSWERED
        );
        $this->assertFalse($this->testObj->isAnswerStatusSelectionWrongAnswered());

        $this->testObj->setAnswerStatusSelection(
            ilTestDynamicQuestionSetFilterSelection::ANSWER_STATUS_FILTER_VALUE_WRONG_ANSWERED
        );
        $this->assertTrue($this->testObj->isAnswerStatusSelectionWrongAnswered());
    }

    public function testSetTaxonomySelection(): void
    {
        $expected = [12591 => "random", 125919 => "array"];
        $this->testObj->setTaxonomySelection($expected);

        $this->assertEquals($expected, $this->testObj->getTaxonomySelection());
    }

    public function testHasSelectedTaxonomy(): void
    {
        $expected = [12591 => "random", 125919 => "array"];
        $this->testObj->setTaxonomySelection($expected);

        $this->assertTrue($this->testObj->hasSelectedTaxonomy(125919));
        $this->assertFalse($this->testObj->hasSelectedTaxonomy(222));
    }

    public function testGetSelectedTaxonomy(): void
    {
        $expected = [12591 => ["random", "array"]];
        $this->testObj->setTaxonomySelection($expected);

        $this->assertEquals($expected[12591], $this->testObj->getSelectedTaxonomy(12591));
    }

    public function testForcedQuestionIds(): void
    {
        $expected = [120, 1250, 12501];
        $this->testObj->setForcedQuestionIds($expected);

        $this->assertEquals($expected, $this->testObj->getForcedQuestionIds());
    }
}
