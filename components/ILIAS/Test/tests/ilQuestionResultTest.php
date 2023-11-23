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

    public function testGetId(): void
    {
        $this->markTestSkipped();
    }

    public function testGetType(): void
    {
        $this->markTestSkipped();
    }

    public function testGetTitle(): void
    {
        $this->markTestSkipped();
    }

    public function testGetUserAnswer(): void
    {
        $this->markTestSkipped();
    }

    public function testGetBestSolution(): void
    {
        $this->markTestSkipped();
    }

    public function testGetQuestionScore(): void
    {
        $this->markTestSkipped();
    }

    public function testGetUserScore(): void
    {
        $this->markTestSkipped();
    }

    public function testGetUserScorePercent(): void
    {
        $this->markTestSkipped();
    }

    public function testGetCorrect(): void
    {
        $this->markTestSkipped();
    }

    public function testGetFeedback(): void
    {
        $this->markTestSkipped();
    }

    public function testIsWorkedThrough(): void
    {
        $this->markTestSkipped();
    }

    public function testIsAnswered(): void
    {
        $this->markTestSkipped();
    }

    public function testGetContentForRecapitulation(): void
    {
        $this->markTestSkipped();
    }
}