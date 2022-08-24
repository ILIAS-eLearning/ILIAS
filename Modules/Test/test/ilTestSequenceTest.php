<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSequenceTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSequenceTest extends ilTestBaseTestCase
{
    private ilTestSequence $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilTestSequence(0, 0, false);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestSequence::class, $this->testObj);
    }
}
