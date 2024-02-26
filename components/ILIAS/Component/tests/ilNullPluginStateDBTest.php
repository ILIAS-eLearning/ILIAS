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

class ilNullPluginStateDBTest extends TestCase
{
    protected function setUp(): void
    {
        $this->db = new \ilNullPluginStateDB();
        $this->data_factory = new Data\Factory();
    }

    public function testIsPluginActivated(): void
    {
        $this->assertFalse($this->db->isPluginActivated("plg1"));
        $this->assertFalse($this->db->isPluginActivated("plg2"));
        $this->assertFalse($this->db->isPluginActivated("plg3"));
    }

    public function testGetCurrentPluginVersion(): void
    {
        $this->assertEquals(null, $this->db->getCurrentPluginVersion("plg1"));
        $this->assertEquals(null, $this->db->getCurrentPluginVersion("plg2"));
        $this->assertEquals(null, $this->db->getCurrentPluginVersion("plg3"));
    }

    public function testGetCurrentPluginDBVersion(): void
    {
        $this->assertEquals(null, $this->db->getCurrentPluginDBVersion("plg1"));
        $this->assertEquals(null, $this->db->getCurrentPluginDBVersion("plg2"));
        $this->assertEquals(null, $this->db->getCurrentPluginVersion("plg3"));
    }

    public function testSetCurrentPluginVersionKnownPlugin(): void
    {
        $PLUGIN_ID = "plg2";
        $VERSION = $this->data_factory->version("1.0.0");
        $DB_VERSION = 23;

        $this->db->setCurrentPluginVersion($PLUGIN_ID, $VERSION, $DB_VERSION);

        $this->assertTrue(true); // Should simply work...
    }

    public function testSetActivation(): void
    {
        $this->db->setActivation("SOME_ID", true);

        $this->assertTrue(true); // Should simply work...
    }


    public function testRemove(): void
    {
        $PLUGIN_ID = "plg1";
        $this->db->remove($PLUGIN_ID);

        $this->assertTrue(true); // Should simply work...
    }
}
