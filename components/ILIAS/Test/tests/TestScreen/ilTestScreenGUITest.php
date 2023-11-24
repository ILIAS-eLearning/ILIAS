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
}