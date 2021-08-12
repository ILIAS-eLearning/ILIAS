<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSessionDynamicQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSessionDynamicQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestSessionDynamicQuestionSet $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSessionDynamicQuestionSet();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSessionDynamicQuestionSet::class, $this->testObj);
    }
}