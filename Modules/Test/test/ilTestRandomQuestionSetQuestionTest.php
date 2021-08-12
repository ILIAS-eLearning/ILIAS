<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestRandomQuestionSetQuestionTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetQuestionTest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetQuestion $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestRandomQuestionSetQuestion();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetQuestion::class, $this->testObj);
    }
}