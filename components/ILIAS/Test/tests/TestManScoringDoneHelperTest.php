<?php

use ILIAS\Test\TestManScoringDoneHelper;

class TestManScoringDoneHelperTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $testManScoringDoneHelper = new TestManScoringDoneHelper();
        $this->assertInstanceOf(TestManScoringDoneHelper::class, $testManScoringDoneHelper);

        $testManScoringDoneHelper = new TestManScoringDoneHelper($this->createMock(ilDBInterface::class));
        $this->assertInstanceOf(TestManScoringDoneHelper::class, $testManScoringDoneHelper);
    }

    public function testIsDone(): void
    {
        $this->markTestSkipped();
    }

    public function  testExists(): void
    {
        $this->markTestSkipped();
    }

    public function testSetDone(): void
    {
        $this->markTestSkipped();
    }
}