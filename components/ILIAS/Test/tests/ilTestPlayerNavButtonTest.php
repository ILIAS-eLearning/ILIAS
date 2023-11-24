<?php

namespace Test\tests;
use ilTestBaseTestCase;
use ilTestPlayerNavButton;

class ilTestPlayerNavButtonTest extends ilTestBaseTestCase
{
    public function testGetInstance(): void
    {
        $this->assertInstanceOf(ilTestPlayerNavButton::class, ilTestPlayerNavButton::getInstance());
    }
}