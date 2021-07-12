<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class ilPluginInfoTest extends TestCase
{
    protected function setUp() : void
    {
        $this->data_factory = new Data\Factory;

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
        $slots[] = $this->pluginslot;

        $this->plugin = new ilPluginInfo(
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            $this->data_factory->version("1.0.0"),
            12,
            $this->data_factory->version("1.0.0"),
            12,
            $this->data_factory->version("6.0"),
            $this->data_factory->version("6.99"),
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            true,
            false,
            true
        );
    }

    public function testGetter()
    {
        $slots = [];
        $this->assertEquals($this->pluginslot, $this->plugin->getPluginSlot());
        $this->assertEquals($this->component, $this->plugin->getComponent());
        $this->assertEquals("plg1", $this->plugin->getId());
        $this->assertEquals("Plugin1", $this->plugin->getName());
        $this->assertTrue($this->plugin->isActivated());
        $this->assertEquals($this->data_factory->version("1.0.0"), $this->plugin->getCurrentVersion());
        $this->assertEquals(12, $this->plugin->getCurrentDBVersion());
        $this->assertEquals($this->data_factory->version("1.0.0"), $this->plugin->getAvailableVersion());
        $this->assertEquals(12, $this->plugin->getAvailableDBVersion());
        $this->assertEquals($this->data_factory->version("6.0"), $this->plugin->getMinimumILIASVersion());
        $this->assertEquals($this->data_factory->version("6.99"), $this->plugin->getMaximumILIASVersion());
        $this->assertEquals("Richard Klees", $this->plugin->getResponsible());
        $this->assertEquals("richard.klees@concepts-and-training.de", $this->plugin->getResponsibleMail());
        $this->assertTrue($this->plugin->supportsLearningProgress());
        $this->assertFalse($this->plugin->supportsExport());
        $this->assertTrue($this->plugin->supportsCLISetup());
    }

    public function testIsInstalled()
    {
        $this->assertTrue($this->plugin->isInstalled());
    }

    public function testIsNotInstalled()
    {
        $this->plugin = new ilPluginInfo(
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            null,
            null,
            $this->data_factory->version("1.0.0"),
            12,
            $this->data_factory->version("6.0"),
            $this->data_factory->version("6.99"),
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            true,
            false,
            true
        );

        $this->assertFalse($this->plugin->isInstalled());
    }

    public function testUpdateIsNotRequired()
    {
        $this->assertFalse($this->plugin->isUpdateRequired());
    }

    public function testUpdateIsNotRequiredNotInstalled()
    {
        $this->plugin = new ilPluginInfo(
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            null,
            null,
            $this->data_factory->version("1.0.0"),
            12,
            $this->data_factory->version("6.0"),
            $this->data_factory->version("6.99"),
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            true,
            false,
            true
        );

        $this->assertFalse($this->plugin->isUpdateRequired());
    }

    public function testUpdateIsRequired()
    {
        $this->plugin = new ilPluginInfo(
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            $this->data_factory->version("2.0.0"),
            11,
            $this->data_factory->version("1.0.0"),
            12,
            $this->data_factory->version("6.0"),
            $this->data_factory->version("6.99"),
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            true,
            false,
            true
        );

        $this->assertTrue($this->plugin->isUpdateRequired());
    }

    public function testIsCompliantToILIAS()
    {
        $this->assertFalse($this->plugin->isCompliantToILIAS($this->data_factory->version("5.4")));
        $this->assertTrue($this->plugin->isCompliantToILIAS($this->data_factory->version("6.5")));
        $this->assertFalse($this->plugin->isCompliantToILIAS($this->data_factory->version("7.1")));
    }
}
