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

namespace ILIAS\Test\Tests\Presentation;

use ILIAS\Test\Presentation\TestScreenGUI;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as Refinery;

class TestScreenGUITest extends \ilTestBaseTestCase
{
    public function testConstruct(): void
    {
        $TestScreenGUI = new TestScreenGUI(
            $this->getTestObjMock(),
            $this->createMock(\ilObjUser::class),
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class),
            $this->createMock(\ilLanguage::class),
            $this->createMock(Refinery::class),
            $this->createMock(\ilCtrl::class),
            $this->createMock(\ilGlobalTemplateInterface::class),
            $this->createMock(HTTPServices::class),
            $this->createMock(\ILIAS\Test\Presentation\TabsManager::class),
            $this->createMock(\ilAccessHandler::class),
            $this->createMock(\ilTestAccess::class),
            $this->createMock(\ilDBInterface::class),
            $this->createMock(\ilRbacSystem::class)
        );
        $this->assertInstanceOf(TestScreenGUI::class, $TestScreenGUI);
    }
}
