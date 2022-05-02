<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPlayerFixedQuestionSetGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPlayerFixedQuestionSetGUITest extends ilTestBaseTestCase
{
    private ilTestPlayerFixedQuestionSetGUI $testObj;

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
        $this->addGlobal_rbacsystem();
        $this->addGlobal_ilUser();
        $this->addGlobal_refinery();

        $this->testObj = new ilTestPlayerFixedQuestionSetGUI(
            $this->createMock(ilObjTest::class)
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestPlayerFixedQuestionSetGUI::class, $this->testObj);
    }
}
