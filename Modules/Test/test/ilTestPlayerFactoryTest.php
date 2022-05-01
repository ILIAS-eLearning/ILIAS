<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPlayerFactoryTest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPlayerFactoryTest extends ilTestBaseTestCase
{
    private ilTestPlayerFactory $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestPlayerFactory($this->createMock(ilObjTest::class));
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestPlayerFactory::class, $this->testObj);
    }

    public function testGetPlayerGUI() : void
    {
        $this->addGlobal_ilUser();
        $this->addGlobal_lng();
        $this->addGlobal_ilias();
        $this->addGlobal_ilDB();
        $this->addGlobal_ilLog();
        $this->addGlobal_ilErr();
        $this->addGlobal_tree();
        $this->addGlobal_ilAppEventHandler();
        $this->addGlobal_objDefinition();
        $this->addGlobal_tpl();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilPluginAdmin();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_refinery();

        $objTest = new ilObjTest();

        $objTest->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_FIXED);
        $testObj = new ilTestPlayerFactory($objTest);
        $this->assertInstanceOf(ilTestPlayerFixedQuestionSetGUI::class, $testObj->getPlayerGUI());

        $objTest->setQuestionSetType(ilObjTest::QUESTION_SET_TYPE_RANDOM);
        $testObj = new ilTestPlayerFactory($objTest);
        $this->assertInstanceOf(ilTestPlayerRandomQuestionSetGUI::class, $testObj->getPlayerGUI());
    }
}
