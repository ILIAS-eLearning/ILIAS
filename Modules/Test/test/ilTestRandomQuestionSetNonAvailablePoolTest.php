<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetNonAvailablePoolTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetNonAvailablePoolTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetNonAvailablePool $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetNonAvailablePool();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetNonAvailablePool::class, $this->testObj);
    }
}