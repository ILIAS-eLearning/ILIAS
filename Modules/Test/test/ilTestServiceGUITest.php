<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestServiceGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestServiceGUITest extends ilTestBaseTestCase
{
    private ilTestServiceGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_lng();
        $this->addGlobal_tpl();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilias();
        $this->addGlobal_tree();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilPluginAdmin();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilObjDataCache();

        $this->testObj = new ilTestServiceGUI($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestServiceGUI::class, $this->testObj);
    }
}