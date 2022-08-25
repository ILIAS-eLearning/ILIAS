<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

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
