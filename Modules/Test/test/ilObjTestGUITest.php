<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjTestGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilObjTestGUITest extends ilTestBaseTestCase
{
    private ilObjTestGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_lng();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilPluginAdmin();
        $this->addGlobal_tree();
        $this->addGlobal_http();
        $this->addGlobal_ilLocator();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilSetting();
        $this->addGlobal_rbacreview();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_filesystem();
        $this->addGlobal_upload();
        $this->addGlobal_objDefinition();
        $this->addGlobal_tpl();
        $this->addGlobal_ilErr();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilias();

        $this->testObj = new ilObjTestGUI();
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilObjTestGUI::class, $this->testObj);
    }
}