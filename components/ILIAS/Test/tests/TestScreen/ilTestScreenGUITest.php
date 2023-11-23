<?php

namespace TestScreen;

use ilAccessHandler;
use ilCtrl;
use ilDBInterface;
use ilGlobalTemplateInterface;
use ilLanguage;
use ilObjTest;
use ilObjUser;
use ilTabsGUI;
use ilTestBaseTestCase;
use ilTestScreenGUI;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\Services as HTTPServices;

class ilTestScreenGUITest extends ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $ilTestScreenGUI = new ilTestScreenGUI(
            $this->createMock(ilObjTest::class),
            $this->createMock(ilObjUser::class),
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(HTTPServices::class),
            $this->createMock(ilTabsGUI::class),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(ilDBInterface::class),
        );
        $this->assertInstanceOf(ilTestScreenGUI::class, $ilTestScreenGUI);
    }

    public function testExecuteCommand(): void
    {
        $this->markTestSkipped();
    }

    public function testTestScreen(): void
    {
        $this->markTestSkipped();
    }

    public function testHandleRenderMessageBox(): void
    {
        $this->markTestSkipped();
    }

    public function testHandleRenderIntroduction(): void
    {
        $this->markTestSkipped();
    }

    public function testHandleRenderLauncherTest(): void
    {
        $this->markTestSkipped();
    }

    public function testGetLauncher(): void
    {
        $this->markTestSkipped();
    }

    public function testGetResumeLauncherLink(): void
    {
        $this->markTestSkipped();
    }

    public function testGetModalLauncherLink(): void
    {
        $this->markTestSkipped();
    }

    public function testGetModalLauncherInputs(): void
    {
        $this->markTestSkipped();
    }

    public function testGetModalLauncherMessageBox(): void
    {
        $this->markTestSkipped();
    }

    public function testGetStartLauncherLink(): void
    {
        $this->markTestSkipped();
    }

    public function testEvaluateLauncherModalForm(): void
    {
        $this->markTestSkipped();
    }

    public function testIsUserOutOfProcessingTime(): void
    {
        $this->markTestSkipped();
    }

    public function testHasAvailablePasses(): void
    {
        $this->markTestSkipped();
    }

    public function testLastPassSuspended(): void
    {
        $this->markTestSkipped();
    }

    public function testNewPassCanBeStarted(): void
    {
        $this->markTestSkipped();
    }

    public function testIsModalLauncherNeeded(): void
    {
        $this->markTestSkipped();
    }
}