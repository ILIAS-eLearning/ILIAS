<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSequenceFixedQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSequenceFixedQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestSequenceFixedQuestionSet $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSequenceFixedQuestionSet(0, 0, false);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSequenceFixedQuestionSet::class, $this->testObj);
    }
}
