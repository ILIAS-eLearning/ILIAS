<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTestPassDeletionConfirmationGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPassDeletionConfirmationGUITest extends ilTestBaseTestCase
{
    private MockObject $testEvaluationGUI_mock;

    private MockObject $lng_mock;

    private MockObject $ctrl_mock;

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
        $this->ctrl_mock
            ->expects($this->once())
            ->method('getFormAction')
            ->with($this->testEvaluationGUI_mock);

        new ilTestPassDeletionConfirmationGUI($this->ctrl_mock, $this->lng_mock, $this->testEvaluationGUI_mock);
    }

    public function testBuildFailsWithWrongContext(): void
    {
        $gui = new ilTestPassDeletionConfirmationGUI($this->ctrl_mock, $this->lng_mock, $this->testEvaluationGUI_mock);
        $this->expectException(ilTestException::class);
        $gui->build(20, 5, 'invalidContext');
    }
}
