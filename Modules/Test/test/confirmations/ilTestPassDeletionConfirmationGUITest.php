<?php

declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPassDeletionConfirmationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPassDeletionConfirmationGUITest extends ilTestBaseTestCase
{
    private $testEvaluationGUI_mock;

    private $lng_mock;

    private $ctrl_mock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testEvaluationGUI_mock = $this->createMock(ilTestEvaluationGUI::class);
        $this->lng_mock = $this->createMock(ilLanguage::class);
        $this->ctrl_mock = $this->createMock(ilCtrl::class);
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $instance = new ilTestPassDeletionConfirmationGUI(
            $this->ctrl_mock,
            $this->lng_mock,
            $this->testEvaluationGUI_mock
        );

        $this->assertInstanceOf(ilTestPassDeletionConfirmationGUI::class, $instance);
    }

    public function testConstructor(): void
    {
        $this->ctrl_mock->expects($this->once())
                        ->method("getFormAction")
                        ->with($this->testEvaluationGUI_mock);

        new ilTestPassDeletionConfirmationGUI($this->ctrl_mock, $this->lng_mock, $this->testEvaluationGUI_mock);
    }

    public function testBuildFailsWithWrongContext(): void
    {
        $gui = new ilTestPassDeletionConfirmationGUI($this->ctrl_mock, $this->lng_mock, $this->testEvaluationGUI_mock);
        $this->expectException(ilTestException::class);
        $gui->build(20, 5, "invalidContext");
    }
}
