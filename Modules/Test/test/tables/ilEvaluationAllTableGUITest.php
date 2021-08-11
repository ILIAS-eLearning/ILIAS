<?php declare(strict_types=1);
/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilEvaluationAllTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilEvaluationAllTableGUITest extends ilTestBaseTestCase
{
    private ilEvaluationAllTableGUI $tableGui;
    private ilObjTestGUI $parentObj_mock;
    
    protected function setUp() : void
    {
        parent::setUp();
        
        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock->expects($this->any())
                 ->method("txt")
                 ->willReturnCallback(function () {
                     return "testTranslation";
                 });

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
        $this->setGlobalVariable("ilSetting", $this->createMock(ilSetting::class));
        $this->setGlobalVariable("rbacreview", $this->createMock(ilRbacReview::class));
        $this->setGlobalVariable("ilUser", $this->createMock(ilObjUser::class));

        $this->parentObj_mock = $this->createMock(ilObjTestGUI::class);
        $this->parentObj_mock->object = $this->createMock(ilObjTest::class);
        $this->tableGui = new ilEvaluationAllTableGUI($this->parentObj_mock, "");

        
    }

	public function test_instantiateObject_shouldReturnInstance() : void
    {
        $this->assertInstanceOf(ilEvaluationAllTableGUI::class, $this->tableGui);
    }
}