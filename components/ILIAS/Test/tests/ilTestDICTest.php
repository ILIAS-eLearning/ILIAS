<?php

use ILIAS\DI\Container;

class ilTestDICTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestDIC = new ilTestDIC();
        $this->assertInstanceOf(ilTestDIC::class, $ilTestDIC);
    }

    public function testDic(): void
    {
        $this->assertInstanceOf(Container::class, ilTestDIC::dic());
    }

    public function testBuildDIC(): void
    {
        $this->markTestSkipped();
    }
}