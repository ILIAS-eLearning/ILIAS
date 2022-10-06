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

/**
 * Class ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUITest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI $toolbarGUI;

    protected function setUp(): void
    {
        parent::setUp();

        $ctrl_mock = $this->createMock(ilCtrl::class);
        $lng_mock = $this->createMock(ilLanguage::class);

        $questionSetConfigGui_mock = $this->createMock(ilTestRandomQuestionSetConfigGUI::class);
        $questionSetConfig_mock = $this->createMock(ilTestRandomQuestionSetConfig::class);

        $this->setGlobalVariable("lng", $lng_mock);

        $this->toolbarGUI = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $ctrl_mock,
            $lng_mock,
            $questionSetConfigGui_mock,
            $questionSetConfig_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI::class, $this->toolbarGUI);
    }
}
