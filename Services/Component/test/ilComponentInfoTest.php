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

        $plugins = [];
        $this->pluginslot1 = new ilPluginSlotInfo(
            $this->component,
            "slt1",
            "Slot1",
            $plugins
        );

        $this->pluginslot2 = new ilPluginSlotInfo(
            $this->component,
            "slt2",
            "Slot2",
            $plugins
        );

        $slots[] = $this->pluginslot1;
        $slots[] = $this->pluginslot2;
    }

    public function testGetter()
    {
        $this->assertEquals("mod1", $this->component->getId());
        $this->assertEquals("Modules", $this->component->getType());
        $this->assertEquals("Module1", $this->component->getName());
        $this->assertEquals("Modules/Module1", $this->component->getQualifiedName());
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
        $plugins = [];
        $this->assertCount(2, $pluginslots);
        $this->assertEquals(new ilPluginSlotInfo($this->component, "slt1", "Slot1", $plugins), $pluginslots["slt1"]);
        $this->assertEquals(new ilPluginSlotInfo($this->component, "slt2", "Slot2", $plugins), $pluginslots["slt2"]);
    }

    public function testHasPluginSlotId()
    {
        $this->assertTrue($this->component->hasPluginSlotId("slt1"));
        $this->assertTrue($this->component->hasPluginSlotId("slt2"));
        $this->assertFalse($this->component->hasPluginSlotId("slt3"));
    }

    public function testGetPluginSlotById()
    {
        $plugins = [];
        $this->assertEquals(new ilPluginSlotInfo($this->component, "slt1", "Slot1", $plugins), $this->component->getPluginSlotById("slt1"));
        $this->assertEquals(new ilPluginSlotInfo($this->component, "slt2", "Slot2", $plugins), $this->component->getPluginSlotById("slt2"));
    }

    public function testGetUnknownPluginSlotById()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->component->getPluginSlotById("slt3");
    }

    public function testGetUnknownPluginSlot()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->component->getPluginSlotById("slt3");
    }

    public function testHasPluginSlotName()
    {
        $this->assertTrue($this->component->hasPluginSlotName("Slot1"));
        $this->assertTrue($this->component->hasPluginSlotName("Slot2"));
        $this->assertFalse($this->component->hasPluginSlotName("Slot3"));
    }

    public function testGetPluginSlotByName()
    {
        $plugins = [];
        $this->assertEquals(new ilPluginSlotInfo($this->component, "slt1", "Slot1", $plugins), $this->component->getPluginSlotByName("Slot1"));
        $this->assertEquals(new ilPluginSlotInfo($this->component, "slt2", "Slot2", $plugins), $this->component->getPluginSlotByName("Slot2"));
    }

    public function testGetUnknownPluginSlotByName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->component->getPluginSlotById("Slot3");
    }
}
