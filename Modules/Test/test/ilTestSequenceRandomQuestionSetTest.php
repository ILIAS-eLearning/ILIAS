<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSequenceRandomQuestionSetTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSequenceRandomQuestionSetTest extends ilTestBaseTestCase
{
    private ilTestSequenceRandomQuestionSet $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSequenceRandomQuestionSet(0, 0, false);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSequenceRandomQuestionSet::class, $this->testObj);
    }
}
