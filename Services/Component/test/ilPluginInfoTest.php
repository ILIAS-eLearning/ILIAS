<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use ILIAS\Data;
use PHPUnit\Framework\TestCase;

class ilPluginInfoTest extends TestCase
{
    protected ?Data\Factory $data_factory = null;
    protected ?ilComponentInfo $component = null;
    protected ?ilPluginSlotInfo $pluginslot = null;
    protected ?ilPluginInfo $plugin = null;

    protected function setUp(): void
    {
        $this->data_factory = new Data\Factory();

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
            $this->data_factory->version("6.5"),
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            $this->data_factory->version("1.0.0"),
            12,
            $this->data_factory->version("1.0.0"),
            $this->data_factory->version("6.0"),
            $this->data_factory->version("6.99"),
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            true,
            false,
            true
        );
    }

    public function testGetter(): void
    {
        $this->assertEquals($this->pluginslot, $this->plugin->getPluginSlot());
        $this->assertEquals($this->component, $this->plugin->getComponent());
        $this->assertEquals("plg1", $this->plugin->getId());
        $this->assertEquals("Plugin1", $this->plugin->getName());
        $this->assertTrue($this->plugin->isActivated());
        $this->assertEquals($this->data_factory->version("1.0.0"), $this->plugin->getCurrentVersion());
        $this->assertEquals(12, $this->plugin->getCurrentDBVersion());
        $this->assertEquals($this->data_factory->version("1.0.0"), $this->plugin->getAvailableVersion());
        $this->assertEquals($this->data_factory->version("6.0"), $this->plugin->getMinimumILIASVersion());
        $this->assertEquals($this->data_factory->version("6.99"), $this->plugin->getMaximumILIASVersion());
        $this->assertEquals("Richard Klees", $this->plugin->getResponsible());
        $this->assertEquals("richard.klees@concepts-and-training.de", $this->plugin->getResponsibleMail());
        $this->assertTrue($this->plugin->supportsLearningProgress());
        $this->assertFalse($this->plugin->supportsExport());
        $this->assertTrue($this->plugin->supportsCLISetup());
    }

    public function testIsInstalled(): void
    {
        $this->assertTrue($this->plugin->isInstalled());
    }

    public function testIsNotInstalled(): void
    {
        $this->plugin = new ilPluginInfo(
            $this->data_factory->version("6.5"),
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            null,
            null,
            $this->data_factory->version("1.0.0"),
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

    public function testUpdateIsNotRequired(): void
    {
        $this->assertFalse($this->plugin->isUpdateRequired());
    }

    public function testUpdateIsNotRequiredNotInstalled(): void
    {
        $this->plugin = new ilPluginInfo(
            $this->data_factory->version("6.5"),
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            null,
            null,
            $this->data_factory->version("1.0.0"),
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

    public function testUpdateIsRequired(): void
    {
        $this->plugin = new ilPluginInfo(
            $this->data_factory->version("6.5"),
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            $this->data_factory->version("2.0.0"),
            11,
            $this->data_factory->version("1.0.0"),
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

    public function testIsVersionOld(): void
    {
        $this->assertFalse($this->plugin->isVersionToOld());

        $plugin = new ilPluginInfo(
            $this->data_factory->version("6.5"),
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            $this->data_factory->version("1.0.0"),
            12,
            $this->data_factory->version("2.0.0"),
            $this->data_factory->version("6.0"),
            $this->data_factory->version("6.99"),
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            true,
            false,
            true
        );
        $this->assertFalse($plugin->isVersionToOld());

        $plugin = new ilPluginInfo(
            $this->data_factory->version("6.5"),
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            $this->data_factory->version("1.2.2"),
            12,
            $this->data_factory->version("1.0.0"),
            $this->data_factory->version("6.0"),
            $this->data_factory->version("6.99"),
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            true,
            false,
            true
        );
        $this->assertTrue($plugin->isVersionToOld());
    }

    /**
     * @dataProvider versionCompliance
     */
    public function testIsCompliantToILIAS(Data\Version $version, bool $is_compliant): void
    {
        $plugin = new ilPluginInfo(
            $version,
            $this->pluginslot,
            "plg1",
            "Plugin1",
            true,
            $this->data_factory->version("1.2.2"),
            12,
            $this->data_factory->version("1.0.0"),
            $this->data_factory->version("6.0"),
            $this->data_factory->version("6.99"),
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            true,
            false,
            true
        );
        $this->assertSame($is_compliant, $plugin->isCompliantToILIAS());
    }

    public function versionCompliance(): array
    {
        $data_factory = new Data\Factory();
        return [
            [$data_factory->version("5.4"), false],
            [$data_factory->version("6.5"), true],
            [$data_factory->version("7.1"), false]
        ];
    }

    public function testGetPath(): void
    {
        $this->assertEquals(
            ilComponentRepository::PLUGIN_BASE_PATH . "/" . "Modules/Module1/Slot1/Plugin1",
            $this->plugin->getPath()
        );
    }

    public function testGetClassName(): void
    {
        $this->assertEquals(
            "ilPlugin1Plugin",
            $this->plugin->getClassName()
        );
    }

    public function testGetConfigureClassName(): void
    {
        $this->assertEquals(
            "ilPlugin1ConfigGUI",
            $this->plugin->getConfigGUIClassName()
        );
    }

    /**
     * @dataProvider isActivationPossibleTruthTable
     */
    public function testIsActivationPossible(
        bool $is_installed,
        bool $supports_current_ilias,
        bool $needs_update,
        bool $is_version_to_old,
        bool $is_activation_possible
    ): void {
        $plugin = new class ($is_installed, $supports_current_ilias, $needs_update, $is_version_to_old) extends ilPluginInfo {
            protected bool $is_installed;
            protected bool $supports_current_ilias;
            protected bool $needs_update;
            protected bool $is_version_to_old;

            public function __construct(
                bool $is_installed,
                bool $supports_current_ilias,
                bool $needs_update,
                bool $is_version_to_old
            ) {
                $this->is_installed = $is_installed;
                $this->supports_current_ilias = $supports_current_ilias;
                $this->needs_update = $needs_update;
                $this->is_version_to_old = $is_version_to_old;
            }

            public function isInstalled(): bool
            {
                return $this->is_installed;
            }

            public function isUpdateRequired(): bool
            {
                return $this->needs_update;
            }

            public function isCompliantToILIAS(): bool
            {
                return $this->supports_current_ilias;
            }

            public function isVersionToOld(): bool
            {
                return $this->is_version_to_old;
            }
        };

        $this->assertEquals($is_activation_possible, $plugin->isActivationPossible());
    }

    public function isActivationPossibleTruthTable(): array
    {
        // is_installed, supports_current_ilias, needs_update, is_version_to_old => is_activation_possible
        return [
            [false, false, false, false, false],
            [false, false, true, false, false],
            [false, true, false, false, false],
            [false, true, true, false, false],
            [true, false, false, false, false],
            [true, false, true, false, false],
            [true, true, false, false, true],
            [true, true, true, true, false],
            [false, false, false, true, false],
            [false, false, true, true, false],
            [false, true, false, true, false],
            [false, true, true, true, false],
            [true, false, false, true, false],
            [true, false, true, true, false],
            [true, true, false, true, false],
            [true, true, true, true, false]
        ];
    }

    /**
     * @dataProvider isActiveTruthTable
     */
    public function testIsActive(
        bool $is_installed,
        bool $supports_current_ilias,
        bool $needs_update,
        bool $is_activated,
        bool $is_version_to_old,
        bool $is_activation_possible
    ): void {
        $plugin = new class ($is_installed, $supports_current_ilias, $needs_update, $is_activated, $is_version_to_old) extends ilPluginInfo {
            protected bool $is_installed;
            protected bool $supports_current_ilias;
            protected bool $needs_update;
            protected bool $is_activated;
            protected bool $is_version_to_old;

            public function __construct(
                bool $is_installed,
                bool $supports_current_ilias,
                bool $needs_update,
                bool $is_activated,
                bool $is_version_to_old
            ) {
                $this->is_installed = $is_installed;
                $this->supports_current_ilias = $supports_current_ilias;
                $this->needs_update = $needs_update;
                $this->is_activated = $is_activated;
                $this->is_version_to_old = $is_version_to_old;
            }

            public function isActivated(): bool
            {
                return $this->is_activated;
            }

            public function isInstalled(): bool
            {
                return $this->is_installed;
            }

            public function isUpdateRequired(): bool
            {
                return $this->needs_update;
            }

            public function isCompliantToILIAS(): bool
            {
                return $this->supports_current_ilias;
            }

            public function isVersionToOld(): bool
            {
                return $this->is_version_to_old;
            }
        };

        $this->assertEquals($is_activation_possible, $plugin->isActive());
    }

    public function isActiveTruthTable(): array
    {
        // is_installed, supports_current_ilias, needs_update, is_activated, is_version_to_old => is_active
        return [
            [false, false, false, false, false, false],
            [false, false, false, true, false, false],
            [false, false, true , false, false, false],
            [false, false, true , true, false, false],
            [false, true, false, false, false, false],
            [false, true, false, true, false, false],
            [false, true, true , false, false, false],
            [false, true, true , true, false, false],
            [true, false, false, false, false, false],
            [true, false, false, true, false, false],
            [true, false, true , false, false, false],
            [true, false, true , true, false, false],
            [true, true, false, false, false, false],
            [true, true, false, true, false, true],
            [true, true, true , false, false, false],
            [true, true, true , true, false, false],

            [false, false, false, false, true, false],
            [false, false, false, true, true, false],
            [false, false, true , false, true, false],
            [false, false, true , true, true, false],
            [false, true, false, false, true, false],
            [false, true, false, true, true, false],
            [false, true, true , false, true, false],
            [false, true, true , true, true, false],
            [true, false, false, false, true, false],
            [true, false, false, true, true, false],
            [true, false, true , false, true, false],
            [true, false, true , true, true, false],
            [true, true, false, false, true, false],
            [true, true, false, true, true, false],
            [true, true, true , false, true, false],
            [true, true, true , true, true, false],
        ];
    }


    /**
     * @dataProvider inactivityReasonTable
     */
    public function testGetReasonForInactivity(
        bool $is_installed,
        bool $supports_current_ilias,
        bool $needs_update,
        bool $is_activated,
        bool $is_version_to_old,
        string $inactivity_reason
    ): void {
        $plugin = new class ($is_installed, $supports_current_ilias, $needs_update, $is_activated, $is_version_to_old) extends ilPluginInfo {
            protected bool $is_installed;
            protected bool $supports_current_ilias;
            protected bool $needs_update;
            protected bool $is_activated;
            protected bool $is_version_to_old;

            public function __construct(
                bool $is_installed,
                bool $supports_current_ilias,
                bool $needs_update,
                bool $is_activated,
                bool $is_version_to_old
            ) {
                $this->is_installed = $is_installed;
                $this->supports_current_ilias = $supports_current_ilias;
                $this->needs_update = $needs_update;
                $this->is_activated = $is_activated;
                $this->is_version_to_old = $is_version_to_old;
            }

            public function isActivated(): bool
            {
                return $this->is_activated;
            }

            public function isInstalled(): bool
            {
                return $this->is_installed;
            }

            public function isUpdateRequired(): bool
            {
                return $this->needs_update;
            }

            public function isCompliantToILIAS(): bool
            {
                return $this->supports_current_ilias;
            }

            public function getCurrentVersion(): ?Data\Version
            {
                return $this->current_version;
            }

            public function isVersionToOld(): bool
            {
                return $this->is_version_to_old;
            }
        };

        $this->assertEquals($inactivity_reason, $plugin->getReasonForInactivity());
    }

    public function testGetReasonForInactivityThrowsOnActivePlugin(): void
    {
        $this->expectException(LogicException::class);

        $plugin = new class () extends ilPluginInfo {
            public function __construct()
            {
            }

            public function isActive(): bool
            {
                return true;
            }
        };

        $plugin->getReasonForInactivity();
    }

    public function inactivityReasonTable(): array
    {
        // is_installed, supports_current_ilias, needs_update, is_activated, is_version_to_old => inactivity_reason
        return [
            [false, false, false, false, false, "cmps_needs_matching_ilias_version"],
            [false, false, false, true, false, "cmps_needs_matching_ilias_version"],
            [false, false, true , false, false, "cmps_needs_matching_ilias_version"],
            [false, false, true , true, false, "cmps_needs_matching_ilias_version"],
            [false, true, false, false, false, "cmps_must_installed"],
            [false, true, false, true, false, "cmps_must_installed"],
            [false, true, true , false, false, "cmps_must_installed"],
            [false, true, true , true, false, "cmps_must_installed"],
            [true, false, false, false, false, "cmps_needs_matching_ilias_version"],
            [true, false, false, true, false, "cmps_needs_matching_ilias_version"],
            [true, false, true , false, false, "cmps_needs_matching_ilias_version"],
            [true, false, true , true, false, "cmps_needs_matching_ilias_version"],
            [true, true, false, false, false, "cmps_not_activated"],
            [true, true, true , false, false, "cmps_needs_update"],
            [true, true, true , true, false, "cmps_needs_update"],
            [false, false, false, false, true, "cmps_needs_matching_ilias_version"],
            [false, false, false, true, true, "cmps_needs_matching_ilias_version"],
            [false, false, true , false, true, "cmps_needs_matching_ilias_version"],
            [false, false, true , true, true, "cmps_needs_matching_ilias_version"],
            [false, true, false, false, true, "cmps_must_installed"],
            [false, true, false, true, true, "cmps_must_installed"],
            [false, true, true , false, true, "cmps_must_installed"],
            [false, true, true , true, true, "cmps_must_installed"],
            [true, false, false, false, true, "cmps_needs_matching_ilias_version"],
            [true, false, false, true, true, "cmps_needs_matching_ilias_version"],
            [true, false, true , false, true, "cmps_needs_matching_ilias_version"],
            [true, false, true , true, true, "cmps_needs_matching_ilias_version"],
            [true, true, false, false, true, "cmps_needs_upgrade"],
            [true, true, true , false, true, "cmps_needs_upgrade"],
            [true, true, true , true, true, "cmps_needs_upgrade"],
        ];
    }
}
