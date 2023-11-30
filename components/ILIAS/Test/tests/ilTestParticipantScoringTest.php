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
        $activeId = 210;
        $this->testObj->setActiveId($activeId);
        $this->assertEquals($activeId, $this->testObj->getActiveId());
    }

    public function testScoredPass(): void
    {
        $scoredPass = 210;
        $this->testObj->setScoredPass($scoredPass);
        $this->assertEquals($scoredPass, $this->testObj->getScoredPass());
    }

    public function testAnsweredQuestions(): void
    {
        $answeredQuestions = 210;
        $this->testObj->setAnsweredQuestions($answeredQuestions);
        $this->assertEquals($answeredQuestions, $this->testObj->getAnsweredQuestions());
    }

    public function testTotalQuestions(): void
    {
        $totalQuestions = 210;
        $this->testObj->setTotalQuestions($totalQuestions);
        $this->assertEquals($totalQuestions, $this->testObj->getTotalQuestions());
    }

    public function testReachedPoints(): void
    {
        $reachedPoints = 210;
        $this->testObj->setReachedPoints($reachedPoints);
        $this->assertEquals($reachedPoints, $this->testObj->getReachedPoints());
    }

    public function testMaxPoints(): void
    {
        $maxPoints = 210;
        $this->testObj->setMaxPoints($maxPoints);
        $this->assertEquals($maxPoints, $this->testObj->getMaxPoints());
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
        $finalMark = 'testString';
        $this->testObj->setFinalMark($finalMark);
        $this->assertEquals($finalMark, $this->testObj->getFinalMark());
    }

    public function testGetPercentResult(): void
    {
        $this->assertEquals(0, $this->testObj->getPercentResult());

        $maxPoints = 20;
        $reachedPoints = 12;
        $this->testObj->setMaxPoints($maxPoints);
        $this->testObj->setReachedPoints($reachedPoints);
        $this->assertEquals($reachedPoints / $maxPoints, $this->testObj->getPercentResult());
    }
}
