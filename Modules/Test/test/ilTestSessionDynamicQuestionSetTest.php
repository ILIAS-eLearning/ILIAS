<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSessionDynamicQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSessionDynamicQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestSessionDynamicQuestionSet $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSessionDynamicQuestionSet();
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSessionDynamicQuestionSet::class, $this->testObj);
    }

    public function testGetQuestionSetFilterSelection(): void
    {
        $this->assertInstanceOf(
            ilTestDynamicQuestionSetFilterSelection::class,
            $this->testObj->getQuestionSetFilterSelection()
        );
    }

    public function testCurrentQuestionId(): void
    {
        $this->testObj->setCurrentQuestionId(20);
        $this->assertEquals(20, $this->testObj->getCurrentQuestionId());
    }
}
