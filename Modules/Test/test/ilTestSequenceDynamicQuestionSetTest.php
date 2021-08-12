<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSequenceDynamicQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSequenceDynamicQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestSequenceDynamicQuestionSet $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $db_mock = $this->createMock(ilDBInterface::class);
        $testDynamicQuestionSet_mock = $this->createMock(ilTestDynamicQuestionSet::class);

        $this->testObj = new ilTestSequenceDynamicQuestionSet($db_mock, $testDynamicQuestionSet_mock, 0);
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSequenceDynamicQuestionSet::class, $this->testObj);
    }
}