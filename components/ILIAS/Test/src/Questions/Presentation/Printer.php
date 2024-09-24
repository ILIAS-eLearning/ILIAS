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

declare(strict_types=1);

namespace ILIAS\Test\Questions\Presentation;

use ILIAS\Test\Questions\Properties\Repository as TestQuestionsRepository;
use ILIAS\Test\Presentation\TabsManager;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Language\Language;

class Printer
{
    /**
     * @param array $data <string, mixed>
     */
    public function __construct(
        private readonly UIFactory $ui_factory,
        private \ilGlobalTemplateInterface $tpl,
        private TabsManager $tabs_manager,
        private \ilToolbarGUI $toolbar,
        private readonly Refinery $refinery,
        private readonly Language $lng,
        private readonly \ilCtrl $ctrl,
        private readonly \ilObjUser $user,
        private readonly \ilTestQuestionHeaderBlockBuilder $question_header_builder,
        private readonly \ilObjTest $test_obj,
    ) {
    }

    /**
     * @param array<int> $question_ids
     */
    public function printSelectedQuestions(
        array $print_view_types,
        ?Types $selected_print_view_type,
        array $question_ids
    ): void {
        $this->tabs_manager->resetTabsAndAddBacklink(
            $this->ctrl->getLinkTargetByClass(\ilObjTestGUI::class, \ilObjTestGUI::SHOW_QUESTIONS_CMD)
        );

        $this->toolbar->addComponent(
            $this->ui_factory->viewControl()->mode(
                $print_view_types,
                $this->lng->txt('show_hide_best_solution')
            )->withActive($selected_print_view_type->getLabel($this->lng))
        );
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard('print', 'javascript:window.print();')
        );

        $template = new \ilTemplate('tpl.il_as_tst_print_questions_preview.html', true, true, 'components/ILIAS/Test');

        $this->tpl->addCss(\ilUtil::getStyleSheetLocation('output', 'test_print.css'), 'print');

        $max_points = 0;
        $counter = 1;
        $this->question_header_builder->setHeaderMode($this->test_obj->getTitleOutput());

        foreach ($question_ids as $question_id) {
            $template->setCurrentBlock('question');
            $question_gui = $this->test_obj->createQuestionGUI('', $question_id);
            $question_gui->setRenderPurpose(\assQuestionGUI::RENDER_PURPOSE_PREVIEW);

            $this->question_header_builder->setQuestionTitle($question_gui->getObject()->getTitle());
            $this->question_header_builder->setQuestionPoints($question_gui->getObject()->getMaximumPoints());
            $this->question_header_builder->setQuestionPosition($counter);
            $template->setVariable('QUESTION_HEADER', $this->question_header_builder->getHTML());

            if ($selected_print_view_type === Types::RESULTS_VIEW_TYPE_HIDE) {
                $template->setVariable('SOLUTION_OUTPUT', $question_gui->getPreview(false));
            } else {
                $template->setVariable('TXT_QUESTION_ID', $this->lng->txt('question_id_short'));
                $template->setVariable('QUESTION_ID', $question_gui->getObject()->getId());
                $template->setVariable('SOLUTION_OUTPUT', $question_gui->getSolutionOutput(0, null, false, true, false, false));
            }

            $template->parseCurrentBlock('question');
            $counter++;
            $max_points += $question_gui->getObject()->getMaximumPoints();
        }

        $template->setVariable(
            'TITLE',
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform(
                $this->test_obj->getTitle()
            )
        );
        $template->setVariable('PRINT_TEST', $this->lng->txt('tst_print'));
        $template->setVariable('TXT_PRINT_DATE', $this->lng->txt('date'));
        $template->setVariable(
            'VALUE_PRINT_DATE',
            (new \DateTimeImmutable())
                ->setTimezone(new \DateTimeZone($this->user->getTimeZone()))
                ->format($this->user->getDateTimeFormat()->toString())
        );
        $template->setVariable(
            'TXT_MAXIMUM_POINTS',
            $this->lng->txt('tst_maximum_points')
        );
        $template->setVariable('VALUE_MAXIMUM_POINTS', $max_points);
        $this->tpl->setVariable('PRINT_CONTENT', $template->get());
    }

    public function printAnswers(int $question_id): void
    {
        $this->tabs_manager->resetTabsAndAddBacklink(
            $this->ctrl->getLinkTargetByClass(\ilObjTestGUI::class, \ilObjTestGUI::SHOW_QUESTIONS_CMD)
        );

        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard('print', 'javascript:window.print();')
        );

        $template = new \ilTemplate('tpl.il_as_tst_print_questions_answers.html', true, true, 'components/ILIAS/Test');
        $this->tpl->addCss(\ilUtil::getStyleSheetLocation('output', 'test_print.css'), 'print');

        $question_gui = $this->test_obj->createQuestionGUI('', $question_id);
        $question_gui->setRenderPurpose(\assQuestionGUI::RENDER_PURPOSE_PREVIEW);
        $template->setVariable('TITLE', $question_gui->getObject()->getTitle());
        $template->setVariable('TXT_PRINT_DATE', $this->lng->txt('date'));
        $template->setVariable(
            'VALUE_PRINT_DATE',
            (new \DateTimeImmutable())
                ->setTimezone(new \DateTimeZone($this->user->getTimeZone()))
                ->format($this->user->getDateTimeFormat()->toString())
        );

        $this->tpl->setVariable(
            'PRINT_CONTENT',
            $this->addQuestionResultForTestUsersToTemplate(
                $template,
                $question_gui,
                $this->test_obj->getTestId()
            )->get()
        );
    }

    private function addQuestionResultForTestUsersToTemplate(
        \ilTemplate $template,
        \assQuestionGUI $question_gui,
        int $test_id
    ): \ilTemplate {
        $this->test_obj->setAccessFilteredParticipantList(
            $this->test_obj->buildStatisticsAccessFilteredParticipantList()
        );

        $foundusers = $this->test_obj->getParticipantsForTestAndQuestion(
            $test_id,
            $question_gui->getObject()->getId()
        );

        foreach ($foundusers as $active_id => $passes) {
            if (($resultpass = \ilObjTest::_getResultPass($active_id)) === null) {
                continue;
            }

            for ($i = 0; $i < count($passes); $i++) {
                if ($passes[$i]['pass'] !== $resultpass) {
                    continue;
                }

                $template->setCurrentBlock('question');
                $template = $this->addResultUserInfoToTemplate(
                    $template,
                    $active_id,
                    $resultpass + 1
                );
                $template->setVariable(
                    'SOLUTION_OUTPUT',
                    $question_gui->getSolutionOutput(
                        $active_id,
                        $resultpass,
                        false,
                        false,
                        false,
                        false
                    )
                );
                $template->parseCurrentBlock('question');
            }
        }
        return $template;
    }

    private function addResultUserInfoToTemplate(
        \ilTemplate $template,
        int $active_id,
        int $pass
    ): \ilTemplate {
        $user_id = $this->test_obj->_getUserIdFromActiveId($active_id);
        if (\ilObjUser::_lookupLogin($user_id) !== '') {
            $user = new \ilObjUser($user_id);
        } else {
            $user = new \ilObjUser();
            $user->setLastname($this->lng->txt('deleted_user'));
        }
        if ($user->getMatriculation() !== ''
            && $this->test_obj->getAnonymity() === false) {
            $template->setCurrentBlock('matriculation');
            $template->setVariable('TXT_USR_MATRIC', $this->lng->txt('matriculation'));
            $template->setVariable('VALUE_USR_MATRIC', $user->getMatriculation());
            $template->parseCurrentBlock();
        }

        $invited_user = array_pop($this->test_obj->getInvitedUsers($user_id));
        if (isset($invited_user['clientip']) && $invited_user['clientip'] !== '') {
            $template->setCurrentBlock('client_ip');
            $template->setVariable('TXT_CLIENT_IP', $this->lng->txt('client_ip'));
            $template->setVariable('VALUE_CLIENT_IP', $invited_user['clientip']);
            $template->parseCurrentBlock();
        }

        $template->setVariable('TXT_USR_NAME', $this->lng->txt('name'));
        $uname = $this->test_obj->userLookupFullName($user_id, false);
        $template->setVariable('VALUE_USR_NAME', $uname);
        $template->setVariable('TXT_PASS', $this->lng->txt('scored_pass'));
        $template->setVariable('VALUE_PASS', $pass);
        return $template;
    }
}
