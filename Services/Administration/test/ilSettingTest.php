<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
 * Class ilSettingTest
 * @group needsInstalledILIAS
 */
class ilSettingTest extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = false;

    protected function setUp()
    {
        include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
        ilUnitUtil::performInitialisation();
    }

    /**
     * @group IL_Init
     */
    public function testSetGetSettings()
    {
        $set = new ilSetting("test_module");
        $set->set("foo", "bar");
        $value = $set->get("foo");
        
        $this->assertEquals("bar", $value);
    }

    /**
     * @group IL_Init
     */
    public function testDeletion()
    {
        // set two things for two modules
        $set = new ilSetting("test_module");
        $set->set("foo", "bar");
        $set = new ilSetting("test_module2");
        $set->set("foo2", "bar2");
        $set = new ilSetting("test_module");
        $set->deleteAll();

        $value = $set->get("foo", false, true) . "-";		// should be "-" now
        
        $set = new ilSetting("test_module2");
        $value .= $set->get("foo2");			// should be "-bar2" now
        
        $this->assertEquals("-bar2", $value);
    }

    /**
     * @group IL_Init
     */
    public function testLikeDeletion()
    {
        $set = new ilSetting("test_module3");
        $set->set("foo", "plus");
        $set->set("fooplus", "bar");
        $set->set("barplus", "foo");
        $set->deleteLike("foo%");
        
        $value = $set->get("foo") . "-" .
            $set->get("fooplus") . "-" .
            $set->get("barplus");
        
        $this->assertEquals("--foo", $value);
    }
}
