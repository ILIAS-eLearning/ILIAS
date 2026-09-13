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
 *********************************************************************/

use PHPUnit\Framework\TestCase;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class PCPluggedTest extends COPageTestBase
{
    public function testConstruction(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCPlugged($page);
        $this->assertEquals(
            ilPCPlugged::class,
            get_class($pc)
        );
    }

    public function testModifyPageContentPostXslRendersPluginAfterEmptyPlugin(): void
    {
        $page = $this->getEmptyPageWithDom();

        $first_plugin_info = $this->createConfiguredMock(
            ilPluginInfo::class,
            [
                "isActive" => true,
                "getId" => "first_plugin",
            ]
        );
        $second_plugin_info = $this->createConfiguredMock(
            ilPluginInfo::class,
            [
                "isActive" => true,
                "getId" => "second_plugin",
            ]
        );

        $first_plugin_gui = $this->createMock(ilPageComponentPluginGUI::class);
        $first_plugin_gui->method("getElementHTML")
            ->willReturn("");
        $first_plugin = $this->createMock(ilPageComponentPlugin::class);
        $first_plugin->method("getUIClassInstance")
            ->willReturn($first_plugin_gui);

        $second_plugin_gui = $this->createMock(ilPageComponentPluginGUI::class);
        $second_plugin_gui->method("getElementHTML")
            ->willReturn("second plugin html");
        $second_plugin = $this->createMock(ilPageComponentPlugin::class);
        $second_plugin->method("getUIClassInstance")
            ->willReturn($second_plugin_gui);

        $component_repository = $this->createMock(ilComponentRepository::class);
        $component_repository->method("getPluginByName")
            ->willReturnMap([
                ["First", $first_plugin_info],
                ["Second", $second_plugin_info],
            ]);
        $this->setGlobalVariable("component.repository", $component_repository);

        $component_factory = $this->createMock(ilComponentFactory::class);
        $component_factory->method("getPlugin")
            ->willReturnMap([
                ["first_plugin", $first_plugin],
                ["second_plugin", $second_plugin],
            ]);
        $this->setGlobalVariable("component.factory", $component_factory);

        $pc = new ilPCPlugged($page);
        $first_marker = "{{{{{Plugged<pl/>First<pl/>1.0}}}}}";
        $second_marker = "{{{{{Plugged<pl/>Second<pl/>1.0}}}}}";

        $this->assertEquals(
            "second plugin html",
            $pc->modifyPageContentPostXsl($first_marker . $second_marker, "presentation")
        );
    }

    public function testCreate(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCPlugged($page);
        $pc->create($page, "pg", "", "MyPlugin", "1.0");
        $this->assertXmlEquals(
            '<PageObject HierId="pg"><PageContent><Plugged PluginName="MyPlugin" PluginVersion="1.0"></Plugged></PageContent></PageObject>',
            $page->getXMLFromDom()
        );
    }

    public function testProperties(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCPlugged($page);
        $pc->create($page, "pg", "", "MyPlugin", "1.0");
        $pc->setProperties([
            "prop1" => "val1",
            "prop2" => "val2",
        ]);

        $this->assertEquals(
            [
            "prop1" => "val1",
            "prop2" => "val2",
        ],
            $pc->getProperties()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Plugged PluginName="MyPlugin" PluginVersion="1.0"><PluggedProperty Name="prop1">val1</PluggedProperty><PluggedProperty Name="prop2">val2</PluggedProperty></Plugged></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testVersion(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCPlugged($page);
        $pc->create($page, "pg", "", "MyPlugin", "1.0");
        $pc->setPluginVersion("2.0");

        $this->assertEquals(
            "2.0",
            $pc->getPluginVersion()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Plugged PluginName="MyPlugin" PluginVersion="2.0"></Plugged></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }

    public function testName(): void
    {
        $page = $this->getEmptyPageWithDom();
        $pc = new ilPCPlugged($page);
        $pc->create($page, "pg", "", "MyPlugin", "1.0");
        $pc->setPluginName("YourPlugin");

        $this->assertEquals(
            "YourPlugin",
            $pc->getPluginName()
        );

        $page->stripHierIDs();

        $expected = <<<EOT
<PageObject><PageContent><Plugged PluginName="YourPlugin" PluginVersion="1.0"></Plugged></PageContent></PageObject>
EOT;
        $this->assertXmlEquals(
            $expected,
            $page->getXMLFromDom()
        );
    }
}
