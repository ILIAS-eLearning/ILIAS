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

/**
 * Class ilTestRandomQuestionSetPoolDefinitionFormGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetPoolDefinitionFormGUITest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetPoolDefinitionFormGUI $formGui;

    protected function setUp(): void
    {
        parent::setUp();
        $ctrl_mock = $this->createMock(ilCtrl::class);
        $lng_mock = $this->createMock(ilLanguage::class);

        $this->setGlobalVariable("lng", $lng_mock);
        $this->setGlobalVariable("ilCtrl", $ctrl_mock);

        $testRandomQuestionSetConfigGUI_mock = $this->getMockBuilder(
            ilTestRandomQuestionSetConfigGUI::class
        )->disableOriginalConstructor()->getMock();

        $testRandomQuestionSetConfig_mock = $this->getMockBuilder(
            ilTestRandomQuestionSetConfig::class,
        )->disableOriginalConstructor()->getMock();

        $this->formGui = new ilTestRandomQuestionSetPoolDefinitionFormGUI(
            $ctrl_mock,
            $lng_mock,
            $this->createMock(ilObjTest::class),
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
        $expected = 'testCommand';

        $this->formGui->setSaveCommand($expected);

        $this->assertEquals($expected, $this->formGui->getSaveCommand());
    }

    protected function testSaveAndNewCommand(): void
    {
        $expected = 'testCommand';

        $this->formGui->setSaveAndNewCommand($expected);

        $this->assertEquals($expected, $this->formGui->getSaveAndNewCommand());
    }
}
