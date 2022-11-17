<?php
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

use PHPUnit\Framework\TestCase;

class ilPluginSlotInfoTest extends TestCase
{
    protected function setUp(): void
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
        $this->plugin1 = new class ($v, $this->pluginslot, "plg1", "Plugin1", true, $v, 0, $v, $v, $v, "", "", false, false, false) extends ilPluginInfo {
            public function isActive(): bool
            {
                return true;
            }
        };
        $plugins["plg1"] = $this->plugin1;

        $this->plugin2 = new class ($v, $this->pluginslot, "plg2", "Plugin2", true, $v, 0, $v, $v, $v, "", "", false, false, false) extends ilPluginInfo {
            public function isActive(): bool
            {
                return false;
            }
        };
        $plugins["plg2"] = $this->plugin2;
    }

    public function testGetter(): void
    {
        $slots = [];
        $this->assertEquals(new ilComponentInfo("mod1", "Modules", "Module1", $slots), $this->pluginslot->getComponent());
        $this->assertEquals("slt1", $this->pluginslot->getId());
        $this->assertEquals("Slot1", $this->pluginslot->getName());
        $this->assertEquals("Modules/Module1/Slot1", $this->pluginslot->getQualifiedName());
    }

    public function testGetPlugins(): void
    {
        $plugins = iterator_to_array($this->pluginslot->getPlugins());
        $this->assertCount(2, $plugins);
        $this->assertEquals($this->plugin1, $plugins["plg1"]);
        $this->assertEquals($this->plugin2, $plugins["plg2"]);
    }

    public function testHasPluginId(): void
    {
        $this->assertTrue($this->pluginslot->hasPluginId("plg1"));
        $this->assertTrue($this->pluginslot->hasPluginId("plg2"));
        $this->assertFalse($this->pluginslot->hasPluginId("plg3"));
    }

    public function testGetPluginById(): void
    {
        $this->assertEquals($this->plugin1, $this->pluginslot->getPluginById("plg1"));
        $this->assertEquals($this->plugin2, $this->pluginslot->getPluginById("plg2"));
    }

    public function testGetUnknownPluginId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->pluginslot->getPluginById("plg3");
    }

    public function testHasPluginName(): void
    {
        $this->assertTrue($this->pluginslot->hasPluginName("Plugin1"));
        $this->assertTrue($this->pluginslot->hasPluginName("Plugin2"));
        $this->assertFalse($this->pluginslot->hasPluginName("Plugin3"));
    }

    public function testGetPluginByName(): void
    {
        $this->assertEquals($this->plugin1, $this->pluginslot->getPluginByName("Plugin1"));
        $this->assertEquals($this->plugin2, $this->pluginslot->getPluginByName("Plugin2"));
    }

    public function testGetUnknownPluginName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->pluginslot->getPluginByName("Plugin3");
    }

    public function testGetPath(): void
    {
        $this->assertEquals(
            ilComponentRepository::PLUGIN_BASE_PATH . "/" . "Modules/Module1/Slot1",
            $this->pluginslot->getPath()
        );
    }

    public function testGetActivePlugins(): void
    {
        $plugins = iterator_to_array($this->pluginslot->getActivePlugins());
        $this->assertCount(1, $plugins);
        $this->assertEquals($this->plugin1, $plugins["plg1"]);
    }

    public function testHasActivePlugins(): void
    {
        $this->assertTrue($this->pluginslot->hasActivePlugins());
    }

    public function testHasNoActivePlugins(): void
    {
        $plugins = [];
        $other_pluginslot = new ilPluginSlotInfo(
            $this->component,
            "slt1",
            "Slot1",
            $plugins
        );

        $this->assertFalse($other_pluginslot->hasActivePlugins());
    }
}
