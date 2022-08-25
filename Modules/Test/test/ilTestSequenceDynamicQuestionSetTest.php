<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSequenceDynamicQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSequenceDynamicQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestSequenceDynamicQuestionSet $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $db_mock = $this->createMock(ilDBInterface::class);
        $testDynamicQuestionSet_mock = $this->createMock(ilTestDynamicQuestionSet::class);

        $this->testObj = new ilTestSequenceDynamicQuestionSet($db_mock, $testDynamicQuestionSet_mock, 17);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSequenceDynamicQuestionSet::class, $this->testObj);
    }

    public function testGetActiveId(): void
    {
        $this->assertEquals(17, $this->testObj->getActiveId());
    }

    public function testPreventCheckedQuestionsFromComingUpEnabled(): void
    {
        $this->testObj->setPreventCheckedQuestionsFromComingUpEnabled(false);
        $this->assertFalse($this->testObj->isPreventCheckedQuestionsFromComingUpEnabled());

        $this->testObj->setPreventCheckedQuestionsFromComingUpEnabled(true);
        $this->assertTrue($this->testObj->isPreventCheckedQuestionsFromComingUpEnabled());
    }

    public function testCurrentQuestionId(): void
    {
        $this->testObj->setCurrentQuestionId(5);
        $this->assertEquals(5, $this->testObj->getCurrentQuestionId());
    }
}
