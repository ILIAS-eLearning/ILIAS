<?php

declare(strict_types=1);

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
