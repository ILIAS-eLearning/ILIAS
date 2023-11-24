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
}