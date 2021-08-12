<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestEvalObjectiveOrientedGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestEvalObjectiveOrientedGUITest extends ilTestBaseTestCase
{
    private ilTestEvalObjectiveOrientedGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->addGlobal_tpl();
        $this->addGlobal_lng();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilias();
        $this->addGlobal_tree();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilPluginAdmin();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilObjDataCache();

        $objTest_mock = $this->createMock(ilObjTest::class);
        $this->testObj = new ilTestEvalObjectiveOrientedGUI($objTest_mock);
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestEvalObjectiveOrientedGUI::class, $this->testObj);
    }
}