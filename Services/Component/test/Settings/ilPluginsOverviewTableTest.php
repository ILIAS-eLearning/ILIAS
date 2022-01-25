<?php declare(strict_types=1);

/* Copyright (c) 2022 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Data\Version;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Component\Dropdown\Standard;
use ILIAS\UI\Component\Button\Shy;

class ilPluginsOverviewTableTest extends TestCase
{
    public function setUp() : void
    {
        $this->parent_gui = $this->createMock(ilObjComponentSettingsGUI::class);
        $this->ctrl = $this->createMock(ilCtrl::class);
        $this->ui = $this->createMock(Factory::class);
        $this->renderer = $this->createMock(Renderer::class);
        $this->lng = $this->createMock(ilLanguage::class);
    }

    public function test_createObject() : void
    {
        $obj = new ilPluginsOverviewTable($this->parent_gui, $this->ctrl, $this->ui, $this->renderer, $this->lng, []);
        $this->assertInstanceOf(ilPluginsOverviewTable::class, $obj);
    }

    public function getImportantFieldData() : array
    {
        return [
            [true, true],
            [true, false],
            [false, true],
            [false, false]
        ];
    }

    /**
     * @dataProvider getImportantFieldData
     */
    public function test_getImportantFields(bool $installed, bool $active) : void
    {
        $ret = [];
        $with = [];
        if ($installed && $active) {
            $with = [["not_installed"], ["installed"], ["inactive"], ["cmps_active"]];
            $ret = ["Nicht installiert", "Installiert", "Inaktiv", "Aktiv"];
        }
        if ($installed && !$active) {
            $with = [["not_installed"], ["installed"], ["inactive"]];
            $ret = ["Nicht installiert", "Installiert", "Inaktiv"];
        }
        if (!$installed && $active) {
            $with = [["not_installed"], ["inactive"], ["cmps_active"]];
            $ret = ["Nicht installiert", "Inaktiv", "Aktiv"];
        }
        if (!$installed && !$active) {
            $with = [["not_installed"], ["inactive"]];
            $ret = ["Nicht installiert", "Inaktiv"];
        }

        $this->lng
            ->expects($this->atLeastOnce())
            ->method("txt")
            ->withConsecutive(...$with)
            ->willReturnOnConsecutiveCalls(...$ret)
        ;

        $obj = new class(
            $this->parent_gui,
            $this->ctrl,
            $this->ui,
            $this->renderer,
            $this->lng,
            []
        ) extends ilPluginsOverviewTable {
            public function getImportantFields(ilPluginInfo $plugin_info) : array
            {
                return parent::getImportantFields($plugin_info);
            }
        };

        $plugin_info = $this->createMock(ilPluginInfo::class);
        $plugin_info
            ->expects($this->once())
            ->method("isInstalled")
            ->willReturn($installed)
        ;
        $plugin_info
            ->expects($this->once())
            ->method("isActive")
            ->willReturn($active)
        ;

        $result1 = "Nicht installiert";
        if ($installed) {
            $result1 = "Installiert";
        }

        $result2 = "Inaktiv";
        if ($active) {
            $result2 = "Aktiv";
        }

        $result = $obj->getImportantFields($plugin_info);

        $this->assertEquals($result1, $result[0]);
        $this->assertEquals($result2, $result[1]);
    }

    public function test_getContent() : void
    {
        $this->lng
            ->expects($this->atLeastOnce())
            ->method("txt")
            ->withConsecutive(
                ["cmps_is_installed"],
                ["yes"],
                ["cmps_is_active"],
                ["yes"],
                ["cmps_needs_update"],
                ["yes"]
            )
            ->willReturnOnConsecutiveCalls(
                "Installiert",
                "Ja",
                "Aktiv",
                "Ja",
                "Benoetigt Update",
                "Ja"
            )
        ;

        $obj = new class(
            $this->parent_gui,
            $this->ctrl,
            $this->ui,
            $this->renderer,
            $this->lng,
            []
        ) extends ilPluginsOverviewTable {
            public function getContent(ilPluginInfo $plugin_info) : array
            {
                return parent::getContent($plugin_info);
            }
        };

        $plugin_info = $this->createMock(ilPluginInfo::class);
        $plugin_info
            ->expects($this->once())
            ->method("isInstalled")
            ->willReturn(true)
        ;
        $plugin_info
            ->expects($this->once())
            ->method("isActive")
            ->willReturn(true)
        ;
        $plugin_info
            ->expects($this->once())
            ->method("isUpdateRequired")
            ->willReturn(true)
        ;

        $result = $obj->getContent($plugin_info);

        $this->assertCount(3, $result);

        foreach ($result as $value) {
            $this->assertEquals("Ja", $value);
        }
    }

    public function test_getFurtherFields() : void
    {
        $this->lng
            ->expects($this->atLeastOnce())
            ->method("txt")
            ->withConsecutive(
                ["cmps_id"],
                ["cmps_plugin_slot"],
                ["cmps_current_version"],
                ["cmps_available_version"],
                ["cmps_current_db_version"],
                ["cmps_ilias_min_version"],
                ["cmps_ilias_max_version"],
                ["cmps_responsible"],
                ["cmps_responsible_mail"],
                ["cmps_supports_learning_progress"],
                ["yes"],
                ["cmps_supports_export"],
                ["yes"],
                ["cmps_supports_cli_setup"],
                ["yes"]
            )
            ->willReturnOnConsecutiveCalls(
                "ID",
                "Plugin Slot",
                "Aktuelle Version",
                "Verfuegbare Version",
                "Aktuelle DB-Version",
                "ILIAS Min-Version",
                "ILIAS Max-Version",
                "Verantwortlicher",
                "Email Verantwortlicher",
                "Lernfortschritt",
                "Ja",
                "Export",
                "Ja",
                "CLI-Setup",
                "Ja"
            )
        ;

        $obj = new class(
            $this->parent_gui,
            $this->ctrl,
            $this->ui,
            $this->renderer,
            $this->lng,
            []
        ) extends ilPluginsOverviewTable {
            public function getFurtherFields(ilPluginInfo $plugin_info) : array
            {
                return parent::getFurtherFields($plugin_info);
            }
        };

        $plugin_slot_info = $this->createMock(ilPluginSlotInfo::class);
        $plugin_slot_info
            ->expects($this->once())
            ->method("getName")
            ->willReturn("Name")
        ;

        $plugin_info = $this->createMock(ilPluginInfo::class);
        $plugin_info
            ->expects($this->once())
            ->method("getId")
            ->willReturn("xvw")
        ;
        $plugin_info
            ->expects($this->once())
            ->method("getPluginSlot")
            ->willReturn($plugin_slot_info)
        ;
        $plugin_info
            ->expects($this->once())
            ->method("getCurrentVersion")
            ->willReturn(new Version("22"))
        ;
        $plugin_info
            ->expects($this->once())
            ->method("getAvailableVersion")
            ->willReturn(new Version("22"))
        ;
        $plugin_info
            ->expects($this->once())
            ->method("getCurrentDBVersion")
            ->willReturn(45)
        ;
        $plugin_info
            ->expects($this->once())
            ->method("getMinimumILIASVersion")
            ->willReturn(new Version("5.4"))
        ;
        $plugin_info
            ->expects($this->once())
            ->method("getMaximumILIASVersion")
            ->willReturn(new Version("8.99"))
        ;
        $plugin_info
            ->expects($this->once())
            ->method("getResponsible")
            ->willReturn("Max Mustermann")
        ;
        $plugin_info
            ->expects($this->once())
            ->method("getResponsibleMail")
            ->willReturn("max@mustermann.de")
        ;
        $plugin_info
            ->expects($this->once())
            ->method("supportsLearningProgress")
            ->willReturn(true)
        ;
        $plugin_info
            ->expects($this->once())
            ->method("supportsExport")
            ->willReturn(true)
        ;
        $plugin_info
            ->expects($this->once())
            ->method("supportsCLISetup")
            ->willReturn(true)
        ;

        $result = $obj->getFurtherFields($plugin_info);

        $this->assertCount(12, $result);
    }

    public function test_boolToString() : void
    {
        $this->lng
            ->expects($this->atLeastOnce())
            ->method("txt")
            ->withConsecutive(
                ["yes"],
                ["no"]
            )
            ->willReturnOnConsecutiveCalls(
                "Ja",
                "Nein"
            )
        ;

        $obj = new class(
            $this->parent_gui,
            $this->ctrl,
            $this->ui,
            $this->renderer,
            $this->lng,
            []
        ) extends ilPluginsOverviewTable {
            public function boolToString(bool $value) : string
            {
                return parent::boolToString($value);
            }
        };

        $result = $obj->boolToString(true);
        $this->assertEquals("Ja", $result);

        $result = $obj->boolToString(false);
        $this->assertEquals("Nein", $result);
    }

    public function test_filterData() : void
    {
        $obj = new class(
            $this->parent_gui,
            $this->ctrl,
            $this->ui,
            $this->renderer,
            $this->lng,
            []
        ) extends ilPluginsOverviewTable {
            public function setFilter(array $filter) : void
            {
                $this->filter = $filter;
            }

            public function filterData(array $data) : array
            {
                return parent::filterData($data);
            }
        };

        $plugin_slot = $this->createMock(ilPluginSlotInfo::class);
        $plugin_slot
            ->expects($this->any())
            ->method("getName")
            ->willReturn("Repository")
        ;

        $component = $this->createMock(ilComponentInfo::class);
        $component
            ->expects($this->any())
            ->method("getQualifiedName")
            ->willReturn("QualifiedName")
        ;

        $plugin_info = $this->createMock(ilPluginInfo::class);
        $plugin_info
            ->expects($this->any())
            ->method("getName")
            ->willReturn("TestRepo")
        ;
        $plugin_info
            ->expects($this->any())
            ->method("getId")
            ->willReturn("xvw")
        ;
        $plugin_info
            ->expects($this->any())
            ->method("isActive")
            ->willReturn(true)
        ;
        $plugin_info
            ->expects($this->any())
            ->method("getPluginSlot")
            ->willReturn($plugin_slot)
        ;
        $plugin_info
            ->expects($this->any())
            ->method("getComponent")
            ->willReturn($component)
        ;

        $obj->setFilter(["plugin_name" => "TestRepo"]);
        $result = $obj->filterData([$plugin_info]);
        $this->assertEquals($plugin_info, $result[0]);

        $obj->setFilter(["plugin_name" => "TestRepoFAIL"]);
        $result = $obj->filterData([$plugin_info]);
        $this->assertCount(0, $result);

        $obj->setFilter(["plugin_id" => "xvw"]);
        $result = $obj->filterData([$plugin_info]);
        $this->assertEquals($plugin_info, $result[0]);

        $obj->setFilter(["plugin_id" => "xvwFAIL"]);
        $result = $obj->filterData([$plugin_info]);
        $this->assertCount(0, $result);

        $obj->setFilter(["plugin_active" => "1"]);
        $result = $obj->filterData([$plugin_info]);
        $this->assertEquals($plugin_info, $result[0]);

        $obj->setFilter(["plugin_active" => "-1"]);
        $result = $obj->filterData([$plugin_info]);
        $this->assertCount(0, $result);

        $obj->setFilter(["slot_name" => ["Repository"]]);
        $result = $obj->filterData([$plugin_info]);
        $this->assertEquals($plugin_info, $result[0]);

        $obj->setFilter(["slot_name" => ["RepositoryFAILQualifiedNameFAIL"]]);
        $result = $obj->filterData([$plugin_info]);
        $this->assertCount(0, $result);

        $obj->setFilter(["component_name" => ["QualifiedName"]]);
        $result = $obj->filterData([$plugin_info]);
        $this->assertEquals($plugin_info, $result[0]);

        $obj->setFilter(["component_name" => ["QualifiedNameFAIL"]]);
        $result = $obj->filterData([$plugin_info]);
        $this->assertCount(0, $result);
    }

    public function test_getData() : void
    {
        $obj = new class(
            $this->parent_gui,
            $this->ctrl,
            $this->ui,
            $this->renderer,
            $this->lng,
            []
        ) extends ilPluginsOverviewTable {
            public function getData() : array
            {
                return parent::getData();
            }
        };

        $result = $obj->getData();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_withData() : void
    {
        $obj = new class(
            $this->parent_gui,
            $this->ctrl,
            $this->ui,
            $this->renderer,
            $this->lng,
            []
        ) extends ilPluginsOverviewTable {
            public function getData() : array
            {
                return parent::getData();
            }
            public function withDataWrapper(array $data)
            {
                return parent::withData($data);
            }
        };

        $obj = $obj->withDataWrapper(["data1", "data2"]);
        $result = $obj->getData();

        $this->assertIsArray($result);
        $this->assertEquals("data1", $result[0]);
        $this->assertEquals("data2", $result[1]);
    }

    public function test_getActions_PluginNotInstalled() : void
    {
        $shy = $this->createMock(Shy::class);

        $standard = $this->createMock(Standard::class);
        $standard
            ->expects($this->once())
            ->method("getItems")
            ->willReturn([$shy])
        ;

        $dropdown = $this->createMock(\ILIAS\UI\Component\Dropdown\Factory::class);
        $dropdown
            ->expects($this->once())
            ->method("standard")
            ->willReturn($standard)
        ;

        $this->ui
            ->expects($this->once())
            ->method("dropdown")
            ->willReturn($dropdown)
        ;

        $obj = new class(
            $this->parent_gui,
            $this->ctrl,
            $this->ui,
            $this->renderer,
            $this->lng,
            [],
            $shy
        ) extends ilPluginsOverviewTable {
            protected Shy $shy;

            public function __construct(
                ilObjComponentSettingsGUI $parent_gui,
                ilCtrl $ctrl,
                Factory $ui,
                Renderer $renderer,
                ilLanguage $lng,
                array $filter,
                Shy $shy
            ) {
                parent::__construct($parent_gui, $ctrl, $ui, $renderer, $lng, $filter);
                $this->shy = $shy;
            }

            public function getActions(ilPluginInfo $plugin_info) : Dropdown
            {
                return parent::getActions($plugin_info);
            }
            protected function setParameter(ilPluginInfo $plugin) : void {}
            protected function clearParameter() : void {}
            protected function getDropdownButton(string $caption, string $command) : Shy
            {
                return $this->shy;
            }
        };

        $plugin_info = $this->createMock(ilPluginInfo::class);
        $plugin_info
            ->expects($this->once())
            ->method("isInstalled")
            ->willReturn(false)
        ;

        $result = $obj->getActions($plugin_info);

        $this->assertInstanceOf(Shy::class, $result->getItems()[0]);
    }

    public function test_getActions_PluginInstalled() : void
    {
        $shy = $this->createMock(Shy::class);

        $standard = $this->createMock(Standard::class);
        $standard
            ->expects($this->once())
            ->method("getItems")
            ->willReturn([$shy])
        ;

        $dropdown = $this->createMock(\ILIAS\UI\Component\Dropdown\Factory::class);
        $dropdown
            ->expects($this->once())
            ->method("standard")
            ->willReturn($standard)
        ;

        $this->ui
            ->expects($this->once())
            ->method("dropdown")
            ->willReturn($dropdown)
        ;

        $obj = new class(
            $this->parent_gui,
            $this->ctrl,
            $this->ui,
            $this->renderer,
            $this->lng,
            [],
            $shy
        ) extends ilPluginsOverviewTable {
            protected Shy $shy;

            public function __construct(
                ilObjComponentSettingsGUI $parent_gui,
                ilCtrl $ctrl,
                Factory $ui,
                Renderer $renderer,
                ilLanguage $lng,
                array $filter,
                Shy $shy
            ) {
                parent::__construct($parent_gui, $ctrl, $ui, $renderer, $lng, $filter);
                $this->shy = $shy;
            }

            public function getActions(ilPluginInfo $plugin_info) : Dropdown
            {
                return parent::getActions($plugin_info);
            }
            protected function setParameter(ilPluginInfo $plugin) : void {}
            protected function clearParameter() : void {}
            protected function hasLang(ilPluginInfo $plugin_info) : bool { return false; }
            protected function getDropdownButton(string $caption, string $command) : Shy
            {
                return $this->shy;
            }
        };

        $plugin_info = $this->createMock(ilPluginInfo::class);
        $plugin_info
            ->expects($this->once())
            ->method("isInstalled")
            ->willReturn(true)
        ;
        $plugin_info
            ->expects($this->once())
            ->method("isCompliantToILIAS")
            ->willReturn(false)
        ;
        $plugin_info
            ->expects($this->once())
            ->method("isActive")
            ->willReturn(false)
        ;
        $plugin_info
            ->expects($this->once())
            ->method("isActivationPossible")
            ->willReturn(false)
        ;
        $plugin_info
            ->expects($this->once())
            ->method("isUpdateRequired")
            ->willReturn(false)
        ;

        $result = $obj->getActions($plugin_info);

        $this->assertInstanceOf(Shy::class, $result->getItems()[0]);
    }
}