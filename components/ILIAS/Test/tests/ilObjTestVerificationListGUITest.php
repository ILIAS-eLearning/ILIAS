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

class ilObjTestVerificationListGUITest extends ilTestBaseTestCase
{
    private ilObjTestVerificationListGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilAccess();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilSetting();
        $this->addGlobal_filesystem();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilLoggerFactory();
        $this->addGlobal_rbacreview();
        $this->addGlobal_ilObjDataCache();

        $this->testObj = new ilObjTestVerificationListGUI();
    }
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilObjTestVerificationListGUI::class, $this->testObj);
    }

    public function testInit(): void
    {
        $this->assertNull($this->testObj->init());

        $reflection  = new ReflectionObject($this->testObj);

        $delete_enabled_property = $reflection->getProperty('delete_enabled');
        $this->assertTrue($delete_enabled_property->getValue($this->testObj));

        $cut_enabled_property = $reflection->getProperty('cut_enabled');
        $this->assertTrue($cut_enabled_property->getValue($this->testObj));

        $copy_enabled_property = $reflection->getProperty('copy_enabled');
        $this->assertTrue($copy_enabled_property->getValue($this->testObj));

        $subscribe_enabled_property = $reflection->getProperty('subscribe_enabled');
        $this->assertFalse($subscribe_enabled_property->getValue($this->testObj));

        $link_enabled_property = $reflection->getProperty('link_enabled');
        $this->assertFalse($link_enabled_property->getValue($this->testObj));

        $info_screen_enabled_property = $reflection->getProperty('info_screen_enabled');
        $this->assertFalse($info_screen_enabled_property->getValue($this->testObj));

        $type_property = $reflection->getProperty('type');
        $this->assertEquals('tstv', $type_property->getValue($this->testObj));

        $gui_class_name_property = $reflection->getProperty('gui_class_name');
        $this->assertEquals(ilObjTestVerificationGUI::class, $gui_class_name_property->getValue($this->testObj));

        $commands_property = $reflection->getProperty('commands');
        $this->assertEquals(
            [['permission' => 'read', 'cmd' => 'view', 'lang_var' => 'show', 'default' => true]],
            $commands_property->getValue($this->testObj),
        );
    }

    public function testGetProperties(): void
    {
        $this->assertEquals([[
            'alert' => false,
            'property' => '',
            'value' => '',
        ]],
            $this->testObj->getProperties(),
        );
    }
}