<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilPluginSlotInfoTest extends TestCase
{
    protected function setUp() : void
    {
        $slots = [];
        $this->component = new ilComponentInfo(
            "mod1",
            "Modules",
            "Module1",
            $slots
        );

        $this->pluginslot = new ilPluginSlotInfo(
            $this->component,
            "slt1",
            "Slot1"
        );
    }

    public function testGetter()
    {
        $slots = [];
        $this->assertEquals(new ilComponentInfo("mod1", "Modules", "Module1", $slots), $this->pluginslot->getComponent());
        $this->assertEquals("slt1", $this->pluginslot->getId());
        $this->assertEquals("Slot1", $this->pluginslot->getName());
    }
}
