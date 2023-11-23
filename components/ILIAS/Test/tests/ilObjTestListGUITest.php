<?php

class ilObjTestListGUITest extends ilTestBaseTestCase
{
    private ilObjTestListGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_ilAccess();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilSetting();
        $this->addGlobal_rbacsystem();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilLoggerFactory();
        $this->addGlobal_filesystem();
        $this->addGlobal_rbacreview();
        $this->addGlobal_ilObjDataCache();

        $this->testObj = new ilObjTestListGUI(1);
    }
    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilObjTestListGUI::class, $this->testObj);
    }

    public function testInit(): void
    {
        $this->markTestSkipped();
    }

    public function testGetCommandFrame(): void
    {
        $this->markTestSkipped();
    }

    public function testGetProperties(): void
    {
        $this->markTestSkipped();
    }

    public function testGetCommandLink(): void
    {
        $this->markTestSkipped();
    }

    public function testGetCommands(): void
    {
        $this->markTestSkipped();
    }

    public function testHandleUserResultsCommand(): void
    {
        $this->markTestSkipped();
    }

    public function testRemoveUserResultsCommand(): void
    {
        $this->markTestSkipped();
    }

    /**
     * @dataProvider createDefaultCommandDataProvider
     */
    public function testCreateDefaultCommand(array $IO): void
    {
        $this->assertEquals($IO, $this->testObj->createDefaultCommand($IO));
    }

    public function createDefaultCommandDataProvider()
    {
        return [
            [[]],
            [[1]],
            [[1, 2]],
            [[1, 2, 3]],
        ];
    }

    public function testAddCommandLinkParameter(): void
    {
        $this->markTestSkipped();
    }

    public function tesModifyTitleLink(): void
    {
        $this->markTestSkipped();
    }
}