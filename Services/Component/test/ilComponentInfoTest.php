<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

class ilComponentInfoTest extends TestCase
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

        $this->pluginslot1 = new ilPluginSlotInfo(
            $this->component,
            "slt1",
            "Slot1"
        );

        $this->pluginslot2 = new ilPluginSlotInfo(
            $this->component,
            "slt2",
            "Slot2"
        );

        $slots[] = $this->pluginslot1;
        $slots[] = $this->pluginslot2;
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
        $slots = [];
        new ilComponentInfo(
            "id",
            "SomeOtherType",
            "name",
            $slots
        );
    }

    public function testGetPluginsSlots()
    {
        $pluginslots = iterator_to_array($this->component->getPluginSlots());
        $this->assertEquals(2, count($pluginslots));
        $this->assertEquals(new ilPluginSlotInfo($this->component, "slt1", "Slot1"), $pluginslots["slt1"]);
        $this->assertEquals(new ilPluginSlotInfo($this->component, "slt2", "Slot2"), $pluginslots["slt2"]);
    }
}
