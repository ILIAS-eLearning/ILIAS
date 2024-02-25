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

/**
* Unit tests
*
* @author Matheus Zych <mzych@databay.de>
*
* @ingroup components\ILIASTestQuestionPool
*
* This test was automatically generated.
*/
class ilAssQuestionFeedbackEditingGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionFeedbackEditingGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $assQuestion = $this->createMock(assQuestion::class);
        $assQuestion->feedbackOBJ = $this->createMock(ilAssQuestionFeedback::class);
        $assQuestionGUI = $this->createStub(assQuestionGUI::class);
        $assQuestionGUI->method('getObject')->willReturn($assQuestion);
        $ctrl = $this->createMock(ilCtrl::class);
        $access = $this->createMock(ilAccessHandler::class);
        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tabs = $this->createMock(ilTabsGUI::class);
        $lng = $this->createMock(ilLanguage::class);
        $help = $this->createMock(ilHelpGUI::class);
        $request_data_collector = $this->createMock(ILIAS\TestQuestionPool\RequestDataCollector::class);
        $questionrepository = $this->createMock(ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository::class);


        $this->object = new ilAssQuestionFeedbackEditingGUI(
            $assQuestionGUI,
            $ctrl,
            $access,
            $tpl,
            $tabs,
            $lng,
            $help,
            $request_data_collector,
            $questionrepository
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilAssQuestionFeedbackEditingGUI::class, $this->object);
    }
}
