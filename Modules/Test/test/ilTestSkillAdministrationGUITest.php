<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestSkillAdministrationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestSkillAdministrationGUITest extends ilTestBaseTestCase
{
    private ilTestSkillAdministrationGUI $testObj;

    protected function setUp() : void
    {
        parent::setUp();

        $this->testObj = new ilTestSkillAdministrationGUI(
            $this->getIliasMock(),
            $this->createMock(ilCtrl::class),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(ilTabsGUI::class),
            $this->createMock(ilGlobalPageTemplate::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilTree::class),
            $this->createMock(ilPluginAdmin::class),
            $this->createMock(ilObjTest::class),
            201
        );
    }

    public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestSkillAdministrationGUI::class, $this->testObj);
    }
}