<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestRandomQuestionSetPoolDefinitionFormGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetPoolDefinitionFormGUITest extends ilTestBaseTestCase
{
    /**
     * @var ilCtrl|mixed|MockObject
     */
    private $ctrl_mock;
    /**
     * @var ilLanguage|mixed|MockObject
     */
    private $lng_mock;

    // ilTestRandomQuestionSetPoolDefinitionFormGUI
    private $formGui;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ctrl_mock = $this->createMock(ilCtrl::class);
        $this->lng_mock = $this->createMock(ilLanguage::class);

        $this->setGlobalVariable("lng", $this->lng_mock);
        $this->setGlobalVariable("ilCtrl", $this->ctrl_mock);

        $objTest_mock = $this->createMock(ilObjTest::class);
        $testRandomQuestionSetConfigGUI_mock = $this->getMockBuilder(
            ilTestRandomQuestionSetConfigGUI::class
        )->disableOriginalConstructor()->getMock();

        $testRandomQuestionSetConfig_mock = $this->getMockBuilder(
            ilTestRandomQuestionSetConfig::class,
        )->disableOriginalConstructor()->getMock();

        $this->formGui = new ilTestRandomQuestionSetPoolDefinitionFormGUI(
            $this->ctrl_mock,
            $this->lng_mock,
            $objTest_mock,
            $testRandomQuestionSetConfigGUI_mock,
            $testRandomQuestionSetConfig_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetPoolDefinitionFormGUI::class, $this->formGui);
    }

    protected function testSaveCommand(): void
    {
        $expected = "testCommand";

        $this->formGui->setSaveCommand($expected);

        $this->assertEquals($expected, $this->formGui->getSaveCommand());
    }

    protected function testSaveAndNewCommand(): void
    {
        $expected = "testCommand";

        $this->formGui->setSaveAndNewCommand($expected);

        $this->assertEquals($expected, $this->formGui->getSaveAndNewCommand());
    }
}
