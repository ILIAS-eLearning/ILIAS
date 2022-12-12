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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\Data\Version;
use ILIAS\UI\Component\Dropdown\Dropdown;
use ILIAS\UI\Component\Dropdown\Standard;
use ILIAS\UI\Component\Button\Shy;

class ilPluginsOverviewTableTest extends TestCase
{
    protected function setUp(): void
    {
        $this->parent_gui = $this->createMock(ilObjComponentSettingsGUI::class);
        $this->ctrl = $this->createMock(ilCtrl::class);
        $this->ui = $this->createMock(Factory::class);
        $this->renderer = $this->createMock(Renderer::class);
        $this->lng = $this->createMock(ilLanguage::class);
        $this->lng->method("txt")
            ->willReturnCallback(fn ($id) => $id);
    }

    public function testCreateObject(): void
    {
        $obj = new ilPluginsOverviewTable($this->parent_gui, $this->ctrl, $this->ui, $this->renderer, $this->lng, []);
        $this->assertInstanceOf(ilPluginsOverviewTable::class, $obj);
    }

    public function getImportantFieldData(): array
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
    public function testGetImportantFields(bool $installed, bool $active): void
    {
        $obj = new class ($this->parent_gui, $this->ctrl, $this->ui, $this->renderer, $this->lng, []) extends ilPluginsOverviewTable {
            public function getImportantFields(ilPluginInfo $plugin_info): array
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

        $result1 = "not_installed";
        if ($installed) {
            $result1 = "installed";
        }

        $result2 = "inactive";
        if ($active) {
            $result2 = "cmps_active";
        }

        $result = $obj->getImportantFields($plugin_info);

        $this->assertEquals($result1, $result[0]);
        $this->assertEquals($result2, $result[1]);
    }

    public function testGetContent(): void
    {
        $obj = new class ($this->parent_gui, $this->ctrl, $this->ui, $this->renderer, $this->lng, []) extends ilPluginsOverviewTable {
            public function getContent(ilPluginInfo $plugin_info): array
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

        $this->assertIsArray($result);
    }

    public function testBoolToString(): void
    {
        $obj = new class ($this->parent_gui, $this->ctrl, $this->ui, $this->renderer, $this->lng, []) extends ilPluginsOverviewTable {
            public function boolToString(bool $value): string
            {
                return parent::boolToString($value);
            }
        };

        $result = $obj->boolToString(true);
        $this->assertEquals("yes", $result);

        $result = $obj->boolToString(false);
        $this->assertEquals("no", $result);
    }

    public function testFilterData(): void
    {
        $obj = new class ($this->parent_gui, $this->ctrl, $this->ui, $this->renderer, $this->lng, []) extends ilPluginsOverviewTable {
            public function setFilter(array $filter): void
            {
                $this->filter = $filter;
            }

            public function filterData(array $data): array
            {
                return parent::filterData($data);
            }
        };

        $plugin_slot = $this->createMock(ilPluginSlotInfo::class);
        $plugin_slot
            ->method("getName")
            ->willReturn("Repository")
        ;

        $component = $this->createMock(ilComponentInfo::class);
        $component
            ->method("getQualifiedName")
            ->willReturn("QualifiedName")
        ;

        $plugin_info = $this->createMock(ilPluginInfo::class);
        $plugin_info
            ->method("getName")
            ->willReturn("TestRepo")
        ;
        $plugin_info
            ->method("getId")
            ->willReturn("xvw")
        ;
        $plugin_info
            ->method("isActive")
            ->willReturn(true)
        ;
        $plugin_info
            ->method("getPluginSlot")
            ->willReturn($plugin_slot)
        ;
        $plugin_info
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

    public function testGetData(): void
    {
        $obj = new class ($this->parent_gui, $this->ctrl, $this->ui, $this->renderer, $this->lng, []) extends ilPluginsOverviewTable {
            public function getData(): array
            {
                return parent::getData();
            }
        };

        $result = $obj->getData();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testWithData(): void
    {
        $obj = new class ($this->parent_gui, $this->ctrl, $this->ui, $this->renderer, $this->lng, []) extends ilPluginsOverviewTable {
            public function getData(): array
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

    public function testGetActionsPluginNotInstalled(): void
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

        $obj = new class ($this->parent_gui, $this->ctrl, $this->ui, $this->renderer, $this->lng, [], $shy) extends ilPluginsOverviewTable {
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

            public function getActions(ilPluginInfo $plugin_info): Dropdown
            {
                return parent::getActions($plugin_info);
            }
            protected function setParameter(ilPluginInfo $plugin): void
            {
            }
            protected function clearParameter(): void
            {
            }
            protected function getDropdownButton(string $caption, string $command): Shy
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

    public function testGetActionsPluginInstalled(): void
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

        $obj = new class ($this->parent_gui, $this->ctrl, $this->ui, $this->renderer, $this->lng, [], $shy) extends ilPluginsOverviewTable {
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

            public function getActions(ilPluginInfo $plugin_info): Dropdown
            {
                return parent::getActions($plugin_info);
            }
            protected function setParameter(ilPluginInfo $plugin): void
            {
            }
            protected function clearParameter(): void
            {
            }
            protected function hasLang(ilPluginInfo $plugin_info): bool
            {
                return false;
            }
            protected function getDropdownButton(string $caption, string $command): Shy
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
