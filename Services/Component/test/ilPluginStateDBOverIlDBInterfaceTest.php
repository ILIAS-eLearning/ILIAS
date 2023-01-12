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
use ILIAS\Data;

class ilPluginStateDBOverIlDBInterfaceTest extends TestCase
{
    public static array $plugin_data = [
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

    protected function setUp(): void
    {
        $this->il_db = $this->createMock(\ilDBInterface::class);
        $this->data_factory = new Data\Factory();
        $this->db = new \ilPluginStateDBOverIlDBInterface(
            $this->data_factory,
            $this->il_db
        );
    }

    public function testIsPluginActivated(): void
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

    public function testGetCurrentPluginVersion(): void
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

    public function testGetCurrentPluginDBVersion(): void
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

    public function testSetCurrentPluginVersionKnownPlugin(): void
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

    public function testSetCurrentPluginVersionUnknownPlugin(): void
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

    public function testSetActivationNotExistingPlugin(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->db->setActivation("SOME_ID", true);
    }

    public function testSetActivationTrue(): void
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

        $PLUGIN_ID = "plg1";

        $this->il_db->expects($this->once())
            ->method("update")
            ->with(
                "il_plugin",
                [
                    "active" => ["integer", 1],
                ],
                [
                    "plugin_id" => ["text", $PLUGIN_ID],
                ]
            );

        $this->db->setActivation($PLUGIN_ID, true);
    }

    public function testSetActivationFalse(): void
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

        $PLUGIN_ID = "plg1";

        $this->il_db->expects($this->once())
            ->method("update")
            ->with(
                "il_plugin",
                [
                    "active" => ["integer", 0],
                ],
                [
                    "plugin_id" => ["text", $PLUGIN_ID],
                ]
            );

        $this->db->setActivation($PLUGIN_ID, false);
    }


    public function testRemove(): void
    {
        $PLUGIN_ID = "plg1";

        $this->il_db->expects($this->once())
            ->method("quote")
            ->with($PLUGIN_ID, "text")
            ->willReturn("PLUGIN_ID");
        $this->il_db->expects($this->once())
            ->method("manipulate")
            ->with("DELETE FROM il_plugin WHERE plugin_id = PLUGIN_ID");

        $this->db->remove($PLUGIN_ID);
    }
}
