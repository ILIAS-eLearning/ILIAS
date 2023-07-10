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
 * Class ilTestParticipantScoringTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantScoringTest extends ilTestBaseTestCase
{
    private ilTestParticipantScoring $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestParticipantScoring();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestParticipantScoring::class, $this->testObj);
    }

    public function testActiveId(): void
    {
        $this->testObj->setActiveId(210);
        $this->assertEquals(210, $this->testObj->getActiveId());
    }

    public function testScoredPass(): void
    {
        $this->testObj->setScoredPass(210);
        $this->assertEquals(210, $this->testObj->getScoredPass());
    }

    public function testAnsweredQuestions(): void
    {
        $this->testObj->setAnsweredQuestions(210);
        $this->assertEquals(210, $this->testObj->getAnsweredQuestions());
    }

    public function testTotalQuestions(): void
    {
        $this->testObj->setTotalQuestions(210);
        $this->assertEquals(210, $this->testObj->getTotalQuestions());
    }

    public function testReachedPoints(): void
    {
        $this->testObj->setReachedPoints(210);
        $this->assertEquals(210, $this->testObj->getReachedPoints());
    }

    public function testMaxPoints(): void
    {
        $this->testObj->setMaxPoints(210);
        $this->assertEquals(210, $this->testObj->getMaxPoints());
    }

    public function testPassed(): void
    {
        $this->testObj->setPassed(false);
        $this->assertFalse($this->testObj->isPassed());

        $this->testObj->setPassed(true);
        $this->assertTrue($this->testObj->isPassed());
    }

    public function testFinalMark(): void
    {
        $this->testObj->setFinalMark("testString");
        $this->assertEquals("testString", $this->testObj->getFinalMark());
    }

    public function testGetPercentResult(): void
    {
        $this->assertEquals(0, $this->testObj->getPercentResult());

        $this->testObj->setMaxPoints(20);
        $this->testObj->setReachedPoints(12);
        $this->assertEquals(0.6, $this->testObj->getPercentResult());
    }
}
