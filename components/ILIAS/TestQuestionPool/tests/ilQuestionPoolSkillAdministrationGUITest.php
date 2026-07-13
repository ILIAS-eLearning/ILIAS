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

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Skill\Service\SkillUsageService;
use ILIAS\TestQuestionPool\RequestDataCollector;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class ilQuestionPoolSkillAdministrationGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilQuestionPoolSkillAdministrationGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ilQuestionPoolSkillAdministrationGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class),
            $this->createMock(GlobalHttpState::class),
            $this->createMock(Refinery::class),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(ilTabsGUI::class),
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilDBInterface::class),
            $this->createMock(ilComponentRepository::class),
            $this->createMock(ilComponentFactory::class),
            $this->createMock(ilObjQuestionPool::class),
            $this->createMock(HTTP::class),
            $this->createMock(ilToolbarGUI::class),
            $this->createMock(SkillUsageService::class),
            $this->createMock(RequestDataCollector::class),
            0
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilQuestionPoolSkillAdministrationGUI::class, $this->object);
    }
}
