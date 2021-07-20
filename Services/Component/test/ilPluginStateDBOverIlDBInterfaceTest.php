<?php

/* Copyright (c) 2021 Richard Klees <richard.klees@concepts-and-training.de>, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\Data;

class ilPluginStateDBOverIlDBInterfaceTest extends TestCase
{
    static array $plugin_data = [
        [
            "plugin_id" => "plg1",
            "active" => true,
            "last_update_version" => "1.0.1",
            "db_version" => 12
        ],
        [
            "plugin_id" => "plg2",
            "active" => false,
            "last_update_version" => "2.3.4",
            "db_version" => 0
        ]
    ];

    public function setUp() : void
    {
        $this->il_db = $this->createMock(\ilDBInterface::class);
        $this->data_factory = new Data\Factory();
        $this->db = new \ilPluginStateDBOverIlDBInterface(
            $this->data_factory,
            $this->il_db
        );
    }

    public function testIsPluginActivated()
    {
        $handle = $this->createMock(\ilDBStatement::class);

        $this->il_db->expects($this->once())
            ->method("query")
            ->with("SELECT * FROM il_plugin")
            ->willReturn($handle);
        $this->il_db->expects($this->once())
            ->method("fetchAll")
            ->with($handle)
            ->willReturn(self::$plugin_data);

        $this->assertTrue($this->db->isPluginActivated("plg1"));
        $this->assertFalse($this->db->isPluginActivated("plg2"));
        $this->assertFalse($this->db->isPluginActivated("plg3"));
    }

    public function testGetCurrentPluginVersion()
    {
        $handle = $this->createMock(\ilDBStatement::class);

        $this->il_db->expects($this->once())
            ->method("query")
            ->with("SELECT * FROM il_plugin")
            ->willReturn($handle);
        $this->il_db->expects($this->once())
            ->method("fetchAll")
            ->with($handle)
            ->willReturn(self::$plugin_data);

        $this->assertEquals($this->data_factory->version("1.0.1"), $this->db->getCurrentPluginVersion("plg1"));
        $this->assertEquals($this->data_factory->version("2.3.4"), $this->db->getCurrentPluginVersion("plg2"));
        $this->assertEquals(null, $this->db->getCurrentPluginVersion("plg3"));
    }

    public function testGetCurrentPluginDBVersion()
    {
        $handle = $this->createMock(\ilDBStatement::class);

        $this->il_db->expects($this->once())
            ->method("query")
            ->with("SELECT * FROM il_plugin")
            ->willReturn($handle);
        $this->il_db->expects($this->once())
            ->method("fetchAll")
            ->with($handle)
            ->willReturn(self::$plugin_data);

        $this->assertEquals(12, $this->db->getCurrentPluginDBVersion("plg1"));
        $this->assertEquals(0, $this->db->getCurrentPluginDBVersion("plg2"));
        $this->assertEquals(null, $this->db->getCurrentPluginVersion("plg3"));
    }

    public function testSetCurrentPluginVersionKnownPlugin()
    {
        $handle = $this->createMock(\ilDBStatement::class);

        $this->il_db->expects($this->once())
            ->method("query")
            ->with("SELECT * FROM il_plugin")
            ->willReturn($handle);
        $this->il_db->expects($this->once())
            ->method("fetchAll")
            ->with($handle)
            ->willReturn(self::$plugin_data);

        $PLUGIN_ID = "plg2";
        $VERSION = $this->data_factory->version("1.0.0");
        $DB_VERSION = 23;

        $this->il_db->expects($this->once())
            ->method("update")
            ->with(
                "il_plugin",
                [
                    "last_update_version" => ["text", (string) $VERSION],
                    "db_version" => ["integer", $DB_VERSION]
                ],
                [
                    "plugin_id" => ["text", $PLUGIN_ID]
                ]
            );

        $this->db->setCurrentPluginVersion($PLUGIN_ID, $VERSION, $DB_VERSION);
    }

    public function testSetCurrentPluginVersionUnknownPlugin()
    {
        $handle = $this->createMock(\ilDBStatement::class);

        $this->il_db->expects($this->once())
            ->method("query")
            ->with("SELECT * FROM il_plugin")
            ->willReturn($handle);
        $this->il_db->expects($this->once())
            ->method("fetchAll")
            ->with($handle)
            ->willReturn(self::$plugin_data);

        $PLUGIN_ID = "plg3";
        $VERSION = $this->data_factory->version("1.0.0");
        $DB_VERSION = 23;

        $this->il_db->expects($this->once())
            ->method("insert")
            ->with(
                "il_plugin",
                [
                    "plugin_id" => ["text", $PLUGIN_ID],
                    "active" => ["integer", 0],
                    "last_update_version" => ["text", (string) $VERSION],
                    "db_version" => ["integer", $DB_VERSION]
                ]
            );

        $this->db->setCurrentPluginVersion($PLUGIN_ID, $VERSION, $DB_VERSION);
    }
}
