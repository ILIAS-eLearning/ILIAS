<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestParticipantsTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestParticipantsTableGUITest extends ilTestBaseTestCase
{
    private ilTestParticipantsTableGUI $tableGui;
    private ilTestParticipantsGUI $parentObj_mock;
    
    protected function setUp() : void
    {
        parent::setUp();
        
        $lng_mock = $this->createMock(ilLanguage::class);
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $ctrl_mock->expects($this->any())
            ->method("getFormAction")
            ->willReturnCallback(function () {
                return "testFormAction";
            });
        
        $this->setGlobalVariable("lng", $lng_mock);
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);
        $this->setGlobalVariable("tpl", $this->createMock(ilGlobalPageTemplate::class));
        $this->setGlobalVariable("ilPluginAdmin", new ilPluginAdmin());
        $this->setGlobalVariable("ilDB", $this->createMock(ilDBInterface::class));
        
        $this->parentObj_mock = $this->createMock(ilTestParticipantsGUI::class);
        $objTest_mock = $this->createMock(ilObjTest::class);

        $this->parentObj_mock
            ->expects($this->any())
            ->method("getTestObj")
            ->willReturn($objTest_mock);

        $this->parentObj_mock->object = $objTest_mock;
        $this->tableGui = new ilTestParticipantsTableGUI($this->parentObj_mock, "");
    }

	public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilTestParticipantsTableGUI::class, $this->tableGui);
    }
}