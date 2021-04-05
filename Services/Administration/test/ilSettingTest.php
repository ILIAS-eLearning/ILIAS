<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Class ilSettingTest
 * @group needsInstalledILIAS
 */
class ilSettingTest extends TestCase
{
    protected $backupGlobals = false;

    protected function setUp() : void
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
