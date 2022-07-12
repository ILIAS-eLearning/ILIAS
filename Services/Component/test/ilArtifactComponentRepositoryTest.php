<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\Data;

class ilArtifactComponentRepositoryTest extends TestCase
{
    public static array $component_data = [
        "mod1" => ["Modules", "Module1", [
            ["slt1", "Slot1"],
            ["slt2", "Slot2"],
        ]],
        "mod2" => ["Modules", "Module2", [
        ]],
        "ser1" => ["Services", "Service1", [
            ["slt3", "Slot3"]
        ]],
        "ser2" => ["Services", "Service2", [
            ["slt4", "Slot4"]
        ]]
    ];

    public static array $plugin_data = [
        "plg1" => [
            "Modules",
            "Module1",
            "Slot1",
            "Plugin1",
            "1.9.1",
            "8.0",
            "8.999",
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            true,
            false,
            null
        ],
        "plg2" => [
            "Services",
            "Service2",
            "Slot4",
            "Plugin2",
            "2.9.1",
            "8.1",
            "8.999",
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            null,
            true,
            false
        ]
    ];

    protected function setUp() : void
    {
        $this->data_factory = new Data\Factory();
        $this->ilias_version = $this->createMock(Data\Version::class);
        $this->plugin_state_db = new class() implements ilPluginStateDB {
            public function isPluginActivated(string $id) : bool
            {
                return false;
            }
            public function setActivation(string $id, bool $activated) : void
            {
            }
            public function getCurrentPluginVersion(string $id) : ?Data\Version
            {
                return (new Data\Factory())->version("0.9.1");
            }
            public function getCurrentPluginDBVersion(string $id) : ?int
            {
                return 13;
            }
            public function setCurrentPluginVersion(string $id, Data\Version $version, int $db_version) : void
            {
            }
            public function remove(string $id) : void
            {
            }
        };

        $this->db = new class($this->data_factory, $this->plugin_state_db, $this->ilias_version) extends ilArtifactComponentRepository {
            protected function readComponentData() : array
            {
                return ilArtifactComponentRepositoryTest::$component_data;
            }
            protected function readPluginData() : array
            {
                return ilArtifactComponentRepositoryTest::$plugin_data;
            }
            public function _buildDatabase() : void
            {
                $this->buildDatabase();
            }
        };

        $slots1 = [];
        $this->mod1 = new ilComponentInfo(
            "mod1",
            "Modules",
            "Module1",
            $slots1
        );
        $plugins1 = [];
        $this->slt1 = new ilPluginSlotInfo(
            $this->mod1,
            "slt1",
            "Slot1",
            $plugins1
        );
        $this->plg1 = new ilPluginInfo(
            $this->ilias_version,
            $this->slt1,
            "plg1",
            "Plugin1",
            false,
            $this->data_factory->version("0.9.1"),
            13,
            $this->data_factory->version("1.9.1"),
            $this->data_factory->version("8.0"),
            $this->data_factory->version("8.999"),
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            true,
            false,
            true
        );
        $plugins1["plg1"] = $this->plg1;
        $plugins2 = [];
        $this->slt2 = new ilPluginSlotInfo(
            $this->mod1,
            "slt2",
            "Slot2",
            $plugins2
        );
        $slots1 = ["slt1" => $this->slt1, "slt2" => $this->slt2];

        $slots2 = [];
        $this->mod2 = new ilComponentInfo(
            "mod2",
            "Modules",
            "Module2",
            $slots2
        );

        $slots3 = [];
        $this->ser1 = new ilComponentInfo(
            "ser1",
            "Services",
            "Service1",
            $slots3
        );

        $plugins3 = [];
        $this->slt3 = new ilPluginSlotInfo(
            $this->ser1,
            "slt3",
            "Slot3",
            $plugins3
        );
        $slots3 = ["slt3" => $this->slt3];

        $slots4 = [];
        $this->ser2 = new ilComponentInfo(
            "ser2",
            "Services",
            "Service2",
            $slots4
        );
        $plugins4 = [];
        $this->slt4 = new ilPluginSlotInfo(
            $this->ser2,
            "slt4",
            "Slot4",
            $plugins4
        );
        $slots4 = ["slt4" => $this->slt4];
        $this->plg2 = new ilPluginInfo(
            $this->ilias_version,
            $this->slt4,
            "plg2",
            "Plugin2",
            false,
            $this->data_factory->version("0.9.1"),
            13,
            $this->data_factory->version("2.9.1"),
            $this->data_factory->version("8.1"),
            $this->data_factory->version("8.999"),
            "Richard Klees",
            "richard.klees@concepts-and-training.de",
            false,
            true,
            false
        );
        $plugins4["plg2"] = $this->plg2;
    }

    public function testHasComponent() : void
    {
        $this->assertTrue($this->db->hasComponent("Modules", "Module1"));
        $this->assertTrue($this->db->hasComponent("Modules", "Module2"));
        $this->assertTrue($this->db->hasComponent("Services", "Service1"));
        $this->assertTrue($this->db->hasComponent("Services", "Service2"));
        $this->assertFalse($this->db->hasComponent("Modules", "Module3"));
        $this->assertFalse($this->db->hasComponent("Modules", "Module4"));
        $this->assertFalse($this->db->hasComponent("Services", "Service3"));
        $this->assertFalse($this->db->hasComponent("Services", "Service4"));
    }

    public function testHasComponentThrowsOnUnknownType() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->hasComponent("OtherComponent", "Module1");
    }

    public function testHasComponentId() : void
    {
        $this->assertTrue($this->db->hasComponentId("mod1"));
        $this->assertTrue($this->db->hasComponentId("mod2"));
        $this->assertTrue($this->db->hasComponentId("ser1"));
        $this->assertTrue($this->db->hasComponentId("ser2"));
        $this->assertFalse($this->db->hasComponentId("mod3"));
        $this->assertFalse($this->db->hasComponentId("mod4"));
        $this->assertFalse($this->db->hasComponentId("ser3"));
        $this->assertFalse($this->db->hasComponentId("ser4"));
    }

    public function testGetComponents() : void
    {
        $result = iterator_to_array($this->db->getComponents());

        $ids = array_keys($result);
        $expected_ids = ["mod1", "mod2", "ser1", "ser2"];
        sort($ids);
        sort($expected_ids);

        $this->assertEquals($expected_ids, $ids);

        $this->assertEquals($this->mod1, $result["mod1"]);
        $this->assertEquals($this->mod2, $result["mod2"]);
        $this->assertEquals($this->ser1, $result["ser1"]);
        $this->assertEquals($this->ser2, $result["ser2"]);
    }

    public function testGetComponentById() : void
    {
        $this->assertEquals($this->mod1, $this->db->getComponentById("mod1"));
        $this->assertEquals($this->mod2, $this->db->getComponentById("mod2"));
        $this->assertEquals($this->ser1, $this->db->getComponentById("ser1"));
        $this->assertEquals($this->ser2, $this->db->getComponentById("ser2"));
    }

    public function testGetComponentByTypeAndName() : void
    {
        $this->assertEquals($this->mod1, $this->db->getComponentByTypeAndName("Modules", "Module1"));
        $this->assertEquals($this->mod2, $this->db->getComponentByTypeAndName("Modules", "Module2"));
        $this->assertEquals($this->ser1, $this->db->getComponentByTypeAndName("Services", "Service1"));
        $this->assertEquals($this->ser2, $this->db->getComponentByTypeAndName("Services", "Service2"));
    }

    public function testGetComponentByTypeAndNameThrowsOnUnknownComponent1() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->getComponentByTypeAndName("Modules", "Module3");
    }

    public function testGetComponentByTypeAndNameThrowsOnUnknownComponent2() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->getComponentByTypeAndName("OtherComponent", "Service1");
    }

    public function testGetComponentByIdTypeThrowsOnInvalidId() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->getComponentById("some_id");
    }

    public function testGetPluginSlots() : void
    {
        $slots = iterator_to_array($this->db->getPluginSlots());

        $ids = array_keys($slots);
        $expected_ids = ["slt1", "slt2", "slt3", "slt4"];
        sort($ids);
        sort($expected_ids);

        $this->assertEquals($expected_ids, $ids);

        $this->assertEquals($this->slt1, $slots["slt1"]);
        $this->assertEquals($this->slt2, $slots["slt2"]);
        $this->assertEquals($this->slt3, $slots["slt3"]);
        $this->assertEquals($this->slt4, $slots["slt4"]);
    }

    public function testGetPluginslotById() : void
    {
        $this->assertEquals($this->slt1, $this->db->getPluginSlotById("slt1"));
        $this->assertEquals($this->slt2, $this->db->getPluginSlotById("slt2"));
        $this->assertEquals($this->slt3, $this->db->getPluginSlotById("slt3"));
        $this->assertEquals($this->slt4, $this->db->getPluginSlotById("slt4"));
    }

    public function testNoPluginSlot() : void
    {
        $db = new class($this->data_factory, $this->plugin_state_db, $this->ilias_version) extends ilArtifactComponentRepository {
            protected function readComponentData() : array
            {
                return ["mod2" => ["Modules", "Module2", []]];
            }
            protected function readPluginData() : array
            {
                return [];
            }
        };

        $slots = iterator_to_array($db->getPluginSlots());
        $this->assertEquals([], $slots);
    }

    public function testGetPlugins() : void
    {
        $plugins = iterator_to_array($this->db->getPlugins());

        $ids = array_keys($plugins);
        $expected_ids = ["plg1", "plg2"];
        sort($ids);
        sort($expected_ids);

        $this->assertEquals($expected_ids, $ids);

        $this->assertEquals($this->plg1, $plugins["plg1"]);
        $this->assertEquals($this->plg2, $plugins["plg2"]);
    }

    public function testGetPluginById() : void
    {
        $this->assertEquals($this->plg1, $this->db->getPluginById("plg1"));
        $this->assertEquals($this->plg2, $this->db->getPluginById("plg2"));
    }

    public function testGetPluginByName() : void
    {
        $this->assertEquals($this->plg1, $this->db->getPluginByName("Plugin1"));
        $this->assertEquals($this->plg2, $this->db->getPluginByName("Plugin2"));
    }

    public function testUnknownPlugin() : void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->getPluginById("some_id");
    }

    public function testUsesPluginStateDB() : void
    {
        $plugin_state_db = $this->createMock(ilPluginStateDB::class);
        $plugin_state_db->expects($this->once())
            ->method("isPluginActivated")
            ->with("plg1")
            ->willReturn(true);
        $plugin_state_db->expects($this->once())
            ->method("getCurrentPluginVersion")
            ->with("plg1")
            ->willReturn($this->data_factory->version("1.8.0"));
        $plugin_state_db->expects($this->once())
            ->method("getCurrentPluginDBVersion")
            ->with("plg1")
            ->willReturn(42);

        $db = new class($this->data_factory, $plugin_state_db, $this->ilias_version) extends ilArtifactComponentRepository {
            protected function readComponentData() : array
            {
                return ["mod1" => ["Modules", "Module1", [["slt1", "Slot1"]]]];
            }
            protected function readPluginData() : array
            {
                return [
                    "plg1" => [
                        "Modules",
                        "Module1",
                        "Slot1",
                        "Plugin1",
                        "1.9.1",
                        "8.0",
                        "8.999",
                        "Richard Klees",
                        "richard.klees@concepts-and-training.de",
                        true,
                        false,
                        null
                    ]
                ];
            }
        };

        $plugin = $db->getPluginById("plg1");
        $this->assertTrue($plugin->isActivated());
        $this->assertEquals(
            $this->data_factory->version("1.8.0"),
            $plugin->getCurrentVersion()
        );
        $this->assertEquals(42, $plugin->getCurrentDBVersion());
    }

    public function testGetPluginViaComponentAndPluginSlot() : void
    {
        $plg1 = $this->db
            ->getComponentByTypeAndName("Modules", "Module1")
            ->getPluginSlotById("slt1")
            ->getPluginById("plg1");

        $this->assertEquals($this->plg1, $plg1);
    }

    public function testSetCurrentPluginVersionCallsStateDB() : void
    {
        $VERSION = $this->data_factory->version("1000.0.0");
        $DB_VERSION = 1000;

        $plugin_state_db = $this->createMock(ilPluginStateDB::class);
        $plugin_state_db
            ->method("isPluginActivated")
            ->with("plg1")
            ->willReturn(true);
        $plugin_state_db
            ->method("getCurrentPluginVersion")
            ->with("plg1")
            ->willReturn($this->data_factory->version("1.8.0"));
        $plugin_state_db
            ->method("getCurrentPluginDBVersion")
            ->with("plg1")
            ->willReturn(42);
        $plugin_state_db->expects($this->once())
            ->method("setCurrentPluginVersion")
            ->with("plg1", $VERSION, $DB_VERSION);

        $db = new class($this->data_factory, $plugin_state_db, $this->ilias_version) extends ilArtifactComponentRepository {
            protected function readComponentData() : array
            {
                return ["mod1" => ["Modules", "Module1", [["slt1", "Slot1"]]]];
            }
            protected function readPluginData() : array
            {
                return [
                    "plg1" => [
                        "Modules",
                        "Module1",
                        "Slot1",
                        "Plugin1",
                        "1.9.1",
                        "8.0",
                        "8.999",
                        "Richard Klees",
                        "richard.klees@concepts-and-training.de",
                        true,
                        false,
                        null
                    ]
                ];
            }
        };

        $db->setCurrentPluginVersion("plg1", $VERSION, $DB_VERSION);
    }

    public function testSetCurrentPluginVersionCallsStateDBTriggersRebuild() : void
    {
        $VERSION = $this->data_factory->version("1000.0.0");
        $DB_VERSION = 1000;

        $plugin_state_db = $this->createMock(ilPluginStateDB::class);
        $db = new class($this->data_factory, $plugin_state_db, $this->ilias_version) extends ilArtifactComponentRepository {
            public int $build_called = 0;
            protected function buildDatabase() : void
            {
                $this->build_called++;
                parent::buildDatabase();
            }
            protected function readComponentData() : array
            {
                return ["mod1" => ["Modules", "Module1", [["slt1", "Slot1"]]]];
            }
            protected function readPluginData() : array
            {
                return [
                    "plg1" => [
                        "Modules",
                        "Module1",
                        "Slot1",
                        "Plugin1",
                        "1.9.1",
                        "8.0",
                        "8.999",
                        "Richard Klees",
                        "richard.klees@concepts-and-training.de",
                        true,
                        false,
                        null
                    ]
                ];
            }
        };

        $this->assertEquals(1, $db->build_called);

        $db->setCurrentPluginVersion("plg1", $VERSION, $DB_VERSION);

        $this->assertEquals(2, $db->build_called);
    }

    public function testCallBuildDatabaseTwice() : void
    {
        $this->db->_buildDatabase();

        $this->assertEquals($this->mod1, $this->db->getComponentById("mod1"));
        $this->assertEquals($this->mod1, $this->db->getComponentByTypeAndName("Modules", "Module1"));
        $this->assertEquals($this->slt1, $this->db->getPluginSlotById("slt1"));
        $this->assertEquals($this->plg1, $this->db->getPluginById("plg1"));
        $this->assertEquals($this->plg2, $this->db->getPluginByName("Plugin2"));
    }

    public function testSetActivationCallsStateDB() : void
    {
        $plugin_state_db = $this->createMock(ilPluginStateDB::class);

        $db = new class($this->data_factory, $plugin_state_db, $this->ilias_version) extends ilArtifactComponentRepository {
            protected function readComponentData() : array
            {
                return ["mod1" => ["Modules", "Module1", [["slt1", "Slot1"]]]];
            }
            protected function readPluginData() : array
            {
                return [
                    "plg1" => [
                        "Modules",
                        "Module1",
                        "Slot1",
                        "Plugin1",
                        "1.9.1",
                        "8.0",
                        "8.999",
                        "Richard Klees",
                        "richard.klees@concepts-and-training.de",
                        true,
                        false,
                        null
                    ]
                ];
            }
        };

        $plugin_state_db->expects($this->once())
            ->method("setActivation")
            ->with("plg1", true);

        $db->setActivation("plg1", true);
    }

    public function testSetActivationTriggersRebuild() : void
    {
        $plugin_state_db = $this->createMock(ilPluginStateDB::class);
        $db = new class($this->data_factory, $plugin_state_db, $this->ilias_version) extends ilArtifactComponentRepository {
            public int $build_called = 0;
            protected function buildDatabase() : void
            {
                $this->build_called++;
                parent::buildDatabase();
            }
            protected function readComponentData() : array
            {
                return ["mod1" => ["Modules", "Module1", [["slt1", "Slot1"]]]];
            }
            protected function readPluginData() : array
            {
                return [
                    "plg1" => [
                        "Modules",
                        "Module1",
                        "Slot1",
                        "Plugin1",
                        "1.9.1",
                        "8.0",
                        "8.999",
                        "Richard Klees",
                        "richard.klees@concepts-and-training.de",
                        true,
                        false,
                        null
                    ]
                ];
            }
        };

        $this->assertEquals(1, $db->build_called);

        $db->setActivation("plg1", false);

        $this->assertEquals(2, $db->build_called);
    }

    public function testRemoveStateInformationOfCallsStateDB() : void
    {
        $plugin_state_db = $this->createMock(ilPluginStateDB::class);

        $db = new class($this->data_factory, $plugin_state_db, $this->ilias_version) extends ilArtifactComponentRepository {
            protected function readComponentData() : array
            {
                return ["mod1" => ["Modules", "Module1", [["slt1", "Slot1"]]]];
            }
            protected function readPluginData() : array
            {
                return [
                    "plg1" => [
                        "Modules",
                        "Module1",
                        "Slot1",
                        "Plugin1",
                        "1.9.1",
                        "8.0",
                        "8.999",
                        "Richard Klees",
                        "richard.klees@concepts-and-training.de",
                        true,
                        false,
                        null
                    ]
                ];
            }
        };

        $plugin_state_db->expects($this->once())
            ->method("remove")
            ->with("plg1");

        $db->removeStateInformationOf("plg1");
    }

    public function testRemoveStateInformationOfTriggersRebuild() : void
    {
        $plugin_state_db = $this->createMock(ilPluginStateDB::class);
        $db = new class($this->data_factory, $plugin_state_db, $this->ilias_version) extends ilArtifactComponentRepository {
            public int $build_called = 0;
            protected function buildDatabase() : void
            {
                $this->build_called++;
                parent::buildDatabase();
            }
            protected function readComponentData() : array
            {
                return ["mod1" => ["Modules", "Module1", [["slt1", "Slot1"]]]];
            }
            protected function readPluginData() : array
            {
                return [
                    "plg1" => [
                        "Modules",
                        "Module1",
                        "Slot1",
                        "Plugin1",
                        "1.9.1",
                        "8.0",
                        "8.999",
                        "Richard Klees",
                        "richard.klees@concepts-and-training.de",
                        true,
                        false,
                        null
                    ]
                ];
            }
        };

        $this->assertEquals(1, $db->build_called);

        $db->removeStateInformationOf("plg1");

        $this->assertEquals(2, $db->build_called);
    }
}
