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

namespace PageEditor;

use ilTestBaseTestCase;
use ilTestPageGUI;

class ilTestPageGUITest extends ilTestBaseTestCase
{
    private ilTestPageGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilHelp();
        $this->addGlobal_ilToolbar();

        $this->testObj = new ilTestPageGUI('', 0);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestPageGUI::class, $this->testObj);
    }

    /**
     * @dataProvider getTabsDataProvider
     */
    public function testGetTabs(string $input): void
    {
        $this->assertNull($this->testObj->getTabs($input));
    }

    public function getTabsDataProvider(): array
    {
        return [
            [''],
            ['test'],
        ];
    }
}