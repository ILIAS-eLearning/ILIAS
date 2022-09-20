<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestRandomQuestionSetGeneralConfigFormGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetGeneralConfigFormGUITest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetGeneralConfigFormGUI $formGui;
    /**
     * @var ilTestRandomQuestionSetConfig|mixed|MockObject
     */
    private $questionSetConfig_mock;

    protected function setUp(): void
    {
        parent::setUp();
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $lng_mock = $this->createMock(ilLanguage::class);
        $lng_mock->expects($this->any())
                 ->method("txt")
                 ->willReturnCallback([self::class, "lngTxtCallback"]);

        $this->setGlobalVariable("lng", $lng_mock);
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);

        $testObject_mock = $this->createMock(ilObjTest::class);
        $questionSetConfigGui_mock = $this->createMock(ilTestRandomQuestionSetConfigGUI::class);
        $this->questionSetConfig_mock = $this->createMock(ilTestRandomQuestionSetConfig::class);

        $this->formGui = new ilTestRandomQuestionSetGeneralConfigFormGUI(
            $ctrl_mock,
            $lng_mock,
            $testObject_mock,
            $questionSetConfigGui_mock,
            $this->questionSetConfig_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetGeneralConfigFormGUI::class, $this->formGui);
    }

    public function testEditModeEnabled(): void
    {
        $expected = true;

        $this->formGui->setEditModeEnabled($expected);

        $this->assertEquals($expected, $this->formGui->isEditModeEnabled());
    }

    public function testSave(): void
    {
        $this->formGui->build();

        $this->formGui->testOBJ->setTestId(20101259);

        $this->questionSetConfig_mock->expects($this->once())
                                     ->method("saveToDb");

        $this->formGui->save();
    }

    public static function lngTxtCallback(): string
    {
        $args = func_get_args();
        switch ($args[0]) {
            case "tst_rnd_quest_set_cfg_general_form":
                return "testTitle";
            default:
                return "testValue";
        }
    }
}
