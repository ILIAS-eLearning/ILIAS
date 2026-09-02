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
class ilAssQuestionHintRequestGUITest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionHintRequestGUI $object;

    protected function setUp(): void
    {
        parent::setUp();

        $assQuestionGUI = $this->createMock(assQuestionGUI::class);

        $this->object = new ilAssQuestionHintRequestGUI(
            $this->createMock(ilTestPlayerAbstractGUI::class),
            '',
            $assQuestionGUI,
            null,
            $this->createMock(ilCtrl::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ilTabsGUI::class),
            $this->createMock(ILIAS\GlobalScreen\Services::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilAssQuestionHintRequestGUI::class, $this->object);
    }

    public function testShowListUsesQuestionTitleInTestPlayerContext(): void
    {
        $parent_gui = $this->createMock(ilTestPlayerAbstractGUI::class);
        $parent_gui->expects($this->never())->method('getObject');

        $question = $this->createMock(assQuestion::class);
        $question->expects($this->exactly(2))
            ->method('getTitleForHTMLOutput')
            ->willReturn('Question Title');

        $question_gui = $this->createMock(assQuestionGUI::class);
        $question_gui->method('getObject')->willReturn($question);

        $question_hint_list = $this->createMock(ilAssQuestionHintList::class);
        $question_hint_list->method('getTableData')->willReturn([]);

        $question_hint_tracking = $this->createMock(ilAssQuestionHintTracking::class);
        $question_hint_tracking->method('getRequestedHintsList')->willReturn($question_hint_list);

        $ctrl = $this->createMock(ilCtrl::class);
        $ctrl->method('getCmd')->willReturn(ilAssQuestionHintRequestGUI::CMD_SHOW_LIST);
        $ctrl->method('getNextClass')->willReturn('');
        $ctrl->method('getHtml')->willReturn('Hint list');

        $lng = $this->createMock(ilLanguage::class);
        $lng->method('txt')
            ->willReturnCallback(static fn(string $key): string => match ($key) {
                'show_requested_question_hints' => 'Requested Hints',
                default => $key
            });

        $this->setGlobalVariable('ilCtrl', $ctrl);
        $this->setGlobalVariable('lng', $lng);

        $additional_data = $this->createMock(ILIAS\GlobalScreen\ScreenContext\AdditionalData\Collection::class);
        $additional_data->method('exists')
            ->with(ilTestPlayerLayoutProvider::TEST_PLAYER_VIEW_TITLE)
            ->willReturn(true);
        $additional_data->expects($this->once())
            ->method('replace')
            ->with(
                ilTestPlayerLayoutProvider::TEST_PLAYER_VIEW_TITLE,
                'Question Title - Requested Hints'
            );

        $screen_context = $this->createMock(ILIAS\GlobalScreen\ScreenContext\ScreenContext::class);
        $screen_context->method('getAdditionalData')->willReturn($additional_data);

        $context_services = $this->createMock(ILIAS\GlobalScreen\ScreenContext\ContextServices::class);
        $context_services->method('current')->willReturn($screen_context);

        $tool_services = $this->createMock(ILIAS\GlobalScreen\Scope\Tool\ToolServices::class);
        $tool_services->method('context')->willReturn($context_services);

        $global_screen = $this->createMock(ILIAS\GlobalScreen\Services::class);
        $global_screen->method('tool')->willReturn($tool_services);

        $tpl = $this->createMock(ilGlobalTemplateInterface::class);
        $tpl->expects($this->once())->method('setContent')->with('Hint list');

        $object = new ilAssQuestionHintRequestGUI(
            $parent_gui,
            '',
            $question_gui,
            $question_hint_tracking,
            $ctrl,
            $lng,
            $tpl,
            $this->createMock(ilTabsGUI::class),
            $global_screen
        );

        $object->executeCommand();
    }
}
