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

use ILIAS\Test\Scoring\Manual\TestScoring;

/**
 * Class TestScoringTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class TestScoringTest extends ilTestBaseTestCase
{
    private TestScoring $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->testObj = new TestScoring(
            $this->createMock(ilObjTest::class),
            $this->createMock(ilObjUser::class),
            $DIC['ilDB'],
            $DIC['lng']
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(TestScoring::class, $this->testObj);
    }

    public function testPreserveManualScores(): void
    {
        $this->testObj->setPreserveManualScores(false);
        $this->assertFalse($this->testObj->getPreserveManualScores());

        $this->testObj->setPreserveManualScores(true);
        $this->assertTrue($this->testObj->getPreserveManualScores());
    }

    public function testQuestionId(): void
    {
        $questionId = 20;
        $this->testObj->setQuestionId($questionId);
        $this->assertEquals($questionId, $this->testObj->getQuestionId());
    }
}
