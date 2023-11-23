<?php

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