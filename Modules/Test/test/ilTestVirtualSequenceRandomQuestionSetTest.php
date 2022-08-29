<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestVirtualSequenceRandomQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestVirtualSequenceRandomQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestVirtualSequenceRandomQuestionSet $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestVirtualSequenceRandomQuestionSet(
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilObjTest::class),
            $this->createMock(ilTestSequenceFactory::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestVirtualSequenceRandomQuestionSet::class, $this->testObj);
    }
}
