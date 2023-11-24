<?php

class ilTestArchiverTest extends ilTestBaseTestCase
{
    private ilTestArchiver $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilTabs();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilias();

        $this->testObj = new ilTestArchiver(0, 0);
    }
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestArchiver::class, $this->testObj);
    }
}