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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Renderer as UIRenderer;
use Psr\Http\Message\ServerRequestInterface as HttpRequest;

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class ilObjQuestionPoolSettingsGeneralGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilObjQuestionPoolSettingsGeneralGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new ilObjQuestionPoolSettingsGeneralGUI(
            $this->createMock(ilCtrl::class),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ilTabsGUI::class),
            $this->createConfiguredMock(ilObjQuestionPoolGUI::class, [
                'getObject' => $this->createMock(ilObject::class)
            ]),
            $this->createMock(Refinery::class),
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class),
            $this->createMock(HttpRequest::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilObjQuestionPoolSettingsGeneralGUI::class, $this->object);
    }
}