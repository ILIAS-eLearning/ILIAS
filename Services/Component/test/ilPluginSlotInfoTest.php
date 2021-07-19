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

        $plugins = [];
        $this->pluginslot = new ilPluginSlotInfo(
            $this->component,
            "slt1",
            "Slot1",
            $plugins
        );

        $v = $this->createMock(\ILIAS\Data\Version::class);
        $this->plugin1 = new ilPluginInfo(
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            $v,
            0,
            $v,
            0,
            $v,
            $v,
            "",
            "",
            false,
            false,
            false
        );
        $plugins["plg1"] = $this->plugin1;

        $this->plugin2 = new ilPluginInfo(
            $this->pluginslot,
            "plg2",
            "Plugin2",
            true,
            $v,
            0,
            $v,
            0,
            $v,
            $v,
            "",
            "",
            false,
            false,
            false
        );
        $plugins["plg2"] = $this->plugin2;
    }

    public function testGetter()
    {
        $slots = [];
        $this->assertEquals(new ilComponentInfo("mod1", "Modules", "Module1", $slots), $this->pluginslot->getComponent());
        $this->assertEquals("slt1", $this->pluginslot->getId());
        $this->assertEquals("Slot1", $this->pluginslot->getName());
        $this->assertEquals("Modules/Module1/Slot1", $this->pluginslot->getQualifiedName());
    }

    public function testGetPlugins()
    {
        $plugins = iterator_to_array($this->pluginslot->getPlugins());
        $this->assertEquals(2, count($plugins));
        $this->assertEquals($this->plugin1, $plugins["plg1"]);
        $this->assertEquals($this->plugin2, $plugins["plg2"]);
    }

    public function testHasPluginId()
    {
        $this->assertTrue($this->pluginslot->hasPluginId("plg1"));
        $this->assertTrue($this->pluginslot->hasPluginId("plg2"));
        $this->assertFalse($this->pluginslot->hasPluginId("plg3"));
    }

    public function testGetPluginById()
    {
        $this->assertEquals($this->plugin1, $this->pluginslot->getPluginById("plg1"));
        $this->assertEquals($this->plugin2, $this->pluginslot->getPluginById("plg2"));
    }

    public function testGetUnknownPluginId()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->pluginslot->getPluginById("plg3");
    }

    public function testHasPluginName()
    {
        $this->assertTrue($this->pluginslot->hasPluginName("Plugin1"));
        $this->assertTrue($this->pluginslot->hasPluginName("Plugin2"));
        $this->assertFalse($this->pluginslot->hasPluginName("Plugin3"));
    }

    public function testGetPluginByName()
    {
        $this->assertEquals($this->plugin1, $this->pluginslot->getPluginByName("Plugin1"));
        $this->assertEquals($this->plugin2, $this->pluginslot->getPluginByName("Plugin2"));
    }

    public function testGetUnknownPluginName()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->pluginslot->getPluginByName("Plugin3");
    }
}
