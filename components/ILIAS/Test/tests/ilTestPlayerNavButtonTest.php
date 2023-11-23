<?php

namespace Test\tests;
use ilTestBaseTestCase;
use ilTestPlayerNavButton;

class ilTestPlayerNavButtonTest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $this->markTestSkipped();
    }

    public function testSetLeftGlyph(): void
    {
        $this->markTestSkipped();
    }

    public function testSetRightGlyph(): void
    {
        $this->markTestSkipped();
    }

    public function testRenderCaption(): void
    {
        $this->markTestSkipped();
    }

    public function testSetAndGetNextCommand(): void
    {
        $this->markTestSkipped();
    }

    public function testRender(): void
    {
        $this->markTestSkipped();
    }

    public function testGetInstance(): void
    {
        $this->assertInstanceOf(ilTestPlayerNavButton::class, ilTestPlayerNavButton::getInstance());
    }
}