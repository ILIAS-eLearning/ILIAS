<?php

namespace Test\tests;
use ilQuestionResult;
use ilTestBaseTestCase;

class ilQuestionResultTest extends ilTestBaseTestCase
{
    private ilQuestionResult $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testObj = new ilQuestionResult(
            0,
            '',
            '',
            0.0,
            0.0,
           '',
           '',
           '',
           true,
           true,
           '',
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQuestionResult::class, $this->testObj);
    }
}