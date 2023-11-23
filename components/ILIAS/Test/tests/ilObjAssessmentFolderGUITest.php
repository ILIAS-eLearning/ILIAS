<?php

class ilObjAssessmentFolderGUITest extends ilTestBaseTestCase
{
    private ilObjAssessmentFolderGUI $testObj;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addGlobal_rbacsystem();
        $this->addGlobal_ilRbacAdmin();
        $this->addGlobal_rbacreview();
        $this->addGlobal_ilLocator();
        $this->addGlobal_ilUser();
        $this->addGlobal_ilAccess();
        $this->addGlobal_ilSetting();
        $this->addGlobal_ilToolbar();
        $this->addGlobal_ilCtrl();
        $this->addGlobal_ilTabs();
        $this->addGlobal_objectService();

        $mock = Mockery::mock('overload:ilObjectFactory');
        $mock
            ->shouldReceive('getInstanceByRefId')
            ->andReturn(
                $this->createMock(ilObject::class),
            );

        $this->testObj = new ilObjAssessmentFolderGUI(
            null,
            1, // 0 is not allowed
            true,
            true,
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilObjAssessmentFolderGUI::class, $this->testObj);
    }

    public function testGetAssessmentFolder(): void
    {
        $this->markTestSkipped();
    }

    public function testExecuteCommand(): void
    {
        $this->markTestSkipped();
    }

    public function testSettingsObject(): void
    {
        $this->markTestSkipped();
    }

    public function testBuildSettingsForm(): void
    {
        $this->markTestSkipped();
    }

    public function testSaveSettingsObject(): void
    {
        $this->markTestSkipped();
    }

    public function testShowLogObject(): void
    {
        $this->markTestSkipped();
    }

    public function testExportLogObject(): void
    {
        $this->markTestSkipped();
    }

    public function testGetLogDataOutputForm(): void
    {
        $this->markTestSkipped();
    }

    public function testLogsObject(): void
    {
        $this->markTestSkipped();
    }

    public function testDeleteLogObject(): void
    {
        $this->markTestSkipped();
    }

    public function testLogAdminObject(): void
    {
        $this->markTestSkipped();
    }

    public function testGetAdminTabs(): void
    {
        $this->markTestSkipped();
    }

    public function testGetLogdataSubtabs(): void
    {
        $this->markTestSkipped();
    }

    public function testGetTabs(): void
    {
        $this->markTestSkipped();
    }

    public function testShowLogSettingsObject(): void
    {
        $this->markTestSkipped();
    }

    public function testSaveLogSettingsObject(): void
    {
        $this->markTestSkipped();
    }

    public function testGetLogSettingsForm(): void
    {
        $this->markTestSkipped();
    }
}