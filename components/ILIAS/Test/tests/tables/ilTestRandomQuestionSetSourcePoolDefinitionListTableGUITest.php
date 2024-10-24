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
 * Class ilTestRandomQuestionSetSourcePoolDefinitionListTableGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListTableGUITest extends ilTestBaseTestCase
{
    private ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI $testObj;

    protected function setUp(): void
    {
        global $DIC;
        parent::setUp();

        $this->addGlobal_ilAccess();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilDB();
        $this->addGlobal_http();

        $translator_mock = $this->createMock(ilTestQuestionFilterLabelTranslater::class);
        $this->testObj = new ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI(
            $DIC['ilAccess'],
            $DIC['ilCtrl'],
            $DIC['lng'],
            $DIC['ui.factory'],
            $DIC['ui.renderer'],
            $DIC['http'],
            $translator_mock
        );
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI::class, $this->testObj);
    }
}
