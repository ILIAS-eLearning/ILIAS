<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetStagingPoolQuestionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetStagingPoolQuestionTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetStagingPoolQuestion $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetStagingPoolQuestion($this->createMock(ilDBInterface::class));
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetStagingPoolQuestion::class, $this->testObj);
    }

    public function testTestId(): void
    {
        $this->testObj->setTestId(5);
        $this->assertEquals(5, $this->testObj->getTestId());
    }

    public function testPoolId(): void
    {
        $this->testObj->setPoolId(5);
        $this->assertEquals(5, $this->testObj->getPoolId());
    }

    public function testQuestionId(): void
    {
        $this->testObj->setQuestionId(5);
        $this->assertEquals(5, $this->testObj->getQuestionId());
    }
}
