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
 * Class ilTestScoringTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestScoringTest extends ilTestBaseTestCase
{
    private ilTestScoring $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestScoring($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestScoring::class, $this->testObj);
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
        $this->testObj->setQuestionId(20);
        $this->assertEquals(20, $this->testObj->getQuestionId());
    }
}
