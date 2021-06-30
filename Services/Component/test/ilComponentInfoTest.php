<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilComponentInfoTest extends TestCase
{
    protected function setUp() : void
    {
        $this->component = new ilComponentInfo(
            "mod1",
            "Modules",
            "Module1"
        );
    }

    public function testGetter()
    {
        $this->assertEquals("mod1", $this->component->getId());
        $this->assertEquals("Modules", $this->component->getType());
        $this->assertEquals("Module1", $this->component->getName());
    }

    public function testInvalidTypeThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        new ilComponentInfo(
            "id",
            "SomeOtherType",
            "name"
        );
    }
}
