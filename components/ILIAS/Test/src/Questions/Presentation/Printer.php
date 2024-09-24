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
        private readonly TestQuestionsRepository $questionrepository,
        private readonly \ilTestQuestionHeaderBlockBuilder $question_header_builder,
        private readonly \ilObjTest $test_obj,
    ) {
    }

    /**
     * @param array<int> $question_ids
     */
    public function printSelectedQuestions(
        array $print_view_types,
        array $selected_print_view_type,
        array $question_ids
    ): void {
        $this->tabs_manager->resetTabsAndAddBacklink(
            $this->ctrl->getLinkTargetByClass(\ilObjTestGUI::class, \ilObjTestGUI::SHOW_QUESTIONS_CMD)
        );

        $this->toolbar->addComponent(
            $this->ui_factory->viewControl()->mode(
                $print_view_types,
                $this->lng->txt('show_hide_best_solution')
            )->withActive(key($selected_print_view_type))
        );
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard('print', 'javascript:window.print();')
        );

        $template = new \ilTemplate('tpl.il_as_tst_print_test_confirm.html', true, true, 'components/ILIAS/Test');

        $this->tpl->addCss(\ilUtil::getStyleSheetLocation('output', 'test_print.css'), 'print');

        $print_date = mktime((int) date('H'), (int) date('i'), (int) date('s'), (int) date('m'), (int) date('d'), (int) date('Y'));
        $max_points = 0;
        $counter = 1;
        $this->question_header_builder->setHeaderMode($this->test_obj->getTitleOutput());

        foreach ($question_ids as $question_id) {
            $template->setCurrentBlock('question');
            $question_gui = $this->test_obj->createQuestionGUI('', $question_id);
            if ($print_view_type === self::RESULTS_VIEW_TYPE_HIDE) {
                $question_gui->setRenderPurpose(\assQuestionGUI::RENDER_PURPOSE_PREVIEW);
            } else {
                $question_gui->setPresentationContext(\assQuestionGUI::PRESENTATION_CONTEXT_TEST);
            }

            $this->question_header_builder->setQuestionTitle($question_gui->getObject()->getTitle());
            $this->question_header_builder->setQuestionPoints($question_gui->getObject()->getMaximumPoints());
            $this->question_header_builder->setQuestionPosition($counter);
            $template->setVariable('QUESTION_HEADER', $this->question_header_builder->getHTML());

            if ($print_view_type === self::RESULTS_VIEW_TYPE_HIDE) {
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
            \ilDatePresentation::formatDate(new \ilDateTime($print_date, IL_CAL_UNIX))
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

        $question_content = $this->getQuestionResultForTestUsers($question_id, $this->test_obj->getTestId());
        $question_title = $this->questionrepository->getQuestionPropertiesForQuestionId($question_id)
            ->getGeneralQuestionProperties()->getTitle();
        $page = $this->prepareContentForPrint($question_title, $question_content);
        $this->tpl->setVariable('PRINT_CONTENT', $page);
    }

    private function getQuestionResultForTestUsers(int $question_id, int $test_id): string
    {
        $this->test_obj->setAccessFilteredParticipantList(
            $this->test_obj->buildStatisticsAccessFilteredParticipantList()
        );

        $foundusers = $this->test_obj->getParticipantsForTestAndQuestion($test_id, $question_id);

        $output = '';
        foreach ($foundusers as $active_id => $passes) {
            if (($resultpass = \ilObjTest::_getResultPass($active_id)) === null) {
                continue;
            }

            for ($i = 0; $i < count($passes); $i++) {
                if ($resultpass !== $passes[$i]['pass']) {
                    continue;
                }

                if ($output !== '') {
                    $output .= '<br /><br /><br />';
                }

                // check if re-instantiation is really neccessary
                $question_gui = $this->test_obj->createQuestionGUI('', $passes[$i]['qid']);
                $output .= $this->getResultsHeadUserAndPass($active_id, $resultpass + 1);
                $question_gui->setRenderPurpose(\assQuestionGUI::RENDER_PURPOSE_PREVIEW);
                $output .= $question_gui->getSolutionOutput(
                    $active_id,
                    $resultpass,
                    false,
                    false,
                    false,
                    false
                );
            }
        }
        return $output;
    }

    private function getResultsHeadUserAndPass(int $active_id, int $pass): string
    {
        $template = new \ilTemplate(
            'tpl.il_as_tst_results_head_user_pass.html',
            true,
            true,
            'components/ILIAS/Test'
        );
        $user_id = $this->test_obj->_getUserIdFromActiveId($active_id);
        if (\ilObjUser::_lookupLogin($user_id) !== '') {
            $user = new \ilObjUser($user_id);
        } else {
            $user = new \ilObjUser();
            $user->setLastname($this->lng->txt('deleted_user'));
        }
        if ($user->getMatriculation() !== ''
            && $this->test_obj->getAnonymity() === false) {
            $template->setCurrentBlock('user_matric');
            $template->setVariable('TXT_USR_MATRIC', $this->lng->txt('matriculation'));
            $template->parseCurrentBlock();
            $template->setCurrentBlock('user_matric_value');
            $template->setVariable('VALUE_USR_MATRIC', $user->getMatriculation());
            $template->parseCurrentBlock();
            $template->touchBlock('user_matric_separator');
        }

        $invited_user = array_pop($this->test_obj->getInvitedUsers($user_id));
        if (isset($invited_user['clientip']) && $invited_user['clientip'] !== '') {
            $template->setCurrentBlock('user_clientip');
            $template->setVariable('TXT_CLIENT_IP', $this->lng->txt('client_ip'));
            $template->parseCurrentBlock();
            $template->setCurrentBlock('user_clientip_value');
            $template->setVariable('VALUE_CLIENT_IP', $invited_user['clientip']);
            $template->parseCurrentBlock();
            $template->touchBlock('user_clientip_separator');
        }

        $template->setVariable('TXT_USR_NAME', $this->lng->txt('name'));
        $uname = $this->test_obj->userLookupFullName($user_id, false);
        $template->setVariable('VALUE_USR_NAME', $uname);
        $template->setVariable('TXT_PASS', $this->lng->txt('scored_pass'));
        $template->setVariable('VALUE_PASS', $pass);
        return $template->get();
    }

    private function prepareContentForPrint(string $question_title, string $question_content): string
    {
        $tpl = new \ilTemplate(
            'tpl.question_statistics_print_view.html',
            true,
            true,
            'components/ILIAS/Test'
        );

        $tpl->setVariable('QUESTION_TITLE', $question_title);
        $tpl->setVariable('QUESTION_CONTENT', $question_content);
        return $tpl->get();
    }
}
