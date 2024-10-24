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
 * Class ilTestPlayerRandomQuestionSetGUITest
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestPlayerRandomQuestionSetGUITest extends ilTestBaseTestCase
{
    private ilTestPlayerRandomQuestionSetGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilTabs();
        $this->addGlobal_ilObjDataCache();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_refinery();
        $this->addGlobal_ilHelp();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_ilSetting();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_GlobalScreenService();
        $this->addGlobal_ilNavigationHistory();

        $this->testObj = new ilTestPlayerRandomQuestionSetGUI($this->getTestObjMock());
    }

    public function test_instantiateObject_shouldReturnInstance(): void
    {
        $this->assertInstanceOf(ilTestPlayerRandomQuestionSetGUI::class, $this->testObj);
    }
}
