<?php

use ILIAS\Refinery\Factory as Refinery;

class ilTestShufflerTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestShuffler = new ilTestShuffler($this->createMock(Refinery::class));
        $this->assertInstanceOf(ilTestShuffler::class, $ilTestShuffler);
    }

    public function testGetAnswerShuffleFor(): void
    {
        $this->markTestSkipped();
    }
}