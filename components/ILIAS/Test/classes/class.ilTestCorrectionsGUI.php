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

use ILIAS\Test\Scoring\Manual\TestScoring;
use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\Logging\TestQuestionAdministrationInteractionTypes;
use ILIAS\Test\RequestDataCollector;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as RefineryFactory;

/**
 * Class ilTestCorrectionsGUI
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestCorrectionsGUI
{
    private ?assQuestionGUI $question_gui = null;
    private ilTestAccess $test_access;

    public function __construct(
        private readonly ilDBInterface $database,
        private readonly ilCtrlInterface $ctrl,
        private readonly ilLanguage $language,
        private readonly ilTabsGUI $tabs,
        private readonly ilHelpGUI $help,
        private readonly UIFactory $ui_factory,
        private readonly ilGlobalTemplateInterface $main_tpl,
        private readonly RefineryFactory $refinery,
        private readonly TestLogger $logger,
        private readonly RequestDataCollector $testrequest,
        private readonly ilObjTest $test_obj,
        private readonly ilObjUser $scorer,
    ) {
        $question_id = $this->testrequest->getQuestionId();
        if ($question_id !== 0) {
            $this->question_gui = $this->getQuestionGUI($question_id);
        }
        $this->test_access = new ilTestAccess($test_obj->getRefId());
    }

    public function executeCommand()
    {
        if (!$this->test_access->checkCorrectionsAccess()
            || $this->question_gui !== null
                && !$this->checkQuestion()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        if ($this->testrequest->isset('removeQid') && (int) $this->testrequest->raw('removeQid')) {
            $this->confirmQuestionRemoval();
            return;
        }

        $this->ctrl->saveParameterByClass(self::class, 'q_id');
        $command = $this->ctrl->getCmd('showQuestionList');
        $this->{$command}();
    }

    protected function showQuestion(?ilPropertyFormGUI $form = null)
    {
        $this->setCorrectionTabsContext($this->question_gui, 'question');

        if ($form === null) {
            $form = $this->buildQuestionCorrectionForm($this->question_gui);
        }

        $this->populatePageTitleAndDescription($this->question_gui);
        $this->main_tpl->setContent($form->getHTML());
    }

    protected function buildQuestionCorrectionForm(assQuestionGUI $question_gui): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setId('tst_question_correction');

        $form->setTitle($this->language->txt('tst_corrections_qst_form'));

        $question_gui->populateCorrectionsFormProperties($form);

        $scoring = new TestScoring(
            $this->test_obj,
            $this->scorer,
            $this->database,
            $this->language
        );
        $scoring->setQuestionId($question_gui->getObject()->getId());

        if ($scoring->getNumManualScorings()) {
            $form->addCommandButton('confirmManualScoringReset', $this->language->txt('save'));
        } else {
            $form->addCommandButton('saveQuestion', $this->language->txt('save'));
        }

        return $form;
    }

    protected function confirmManualScoringReset()
    {
        $this->setCorrectionTabsContext($this->question_gui, 'question');

        $scoring = new TestScoring(
            $this->test_obj,
            $this->scorer,
            $this->database,
            $this->language
        );
        $scoring->setQuestionId($this->question_gui->getObject()->getId());

        $confirmation = sprintf(
            $this->language->txt('tst_corrections_manscore_reset_warning'),
            $scoring->getNumManualScorings(),
            $this->question_gui->getObject()->getTitle(),
            $this->question_gui->getObject()->getId()
        );

        $gui = new ilConfirmationGUI();
        $gui->setHeaderText($confirmation);
        $gui->setFormAction($this->ctrl->getFormAction($this));
        $gui->setCancel($this->language->txt('cancel'), 'showQuestion');
        $gui->setConfirm($this->language->txt('confirm'), 'saveQuestion');

        $this->addHiddenItemsFromArray($gui, $this->testrequest->getParsedBody());

        $this->main_tpl->setContent($gui->getHTML());
    }

    protected function saveQuestion()
    {
        $question_gui = $this->question_gui;
        $form = $this->buildQuestionCorrectionForm($question_gui);
        $form->setValuesByPost();

        if (!$form->checkInput()) {
            $question_gui->prepareReprintableCorrectionsForm($form);

            $this->showQuestion($form);
            return;
        }

        $question_gui->saveCorrectionsFormProperties($form);
        $question = $question_gui->getObject();
        $question->setPoints($question_gui->getObject()->getMaximumPoints());
        $question_gui->setObject($question);
        $question_gui->getObject()->saveToDb();

        $scoring = new TestScoring(
            $this->test_obj,
            $this->scorer,
            $this->database,
            $this->language
        );
        $scoring->setPreserveManualScores(false);
        $scoring->setQuestionId($question_gui->getObject()->getId());
        $scoring->recalculateSolutions();

        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logQuestionAdministrationInteraction(
                $question_gui->getObject()->toQuestionAdministrationInteraction(
                    $this->logger->getAdditionalInformationGenerator(),
                    $this->test_obj->getRefId(),
                    TestQuestionAdministrationInteractionTypes::QUESTION_MODIFIED_IN_CORRECTIONS
                )
            );
        }

        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('saved_successfully'), true);
        $this->ctrl->redirectByClass([ilObjTestGUI::class, self::class], 'showQuestion');
    }

    protected function showSolution()
    {
        $question_gui = $this->question_gui;
        $page_gui = new ilAssQuestionPageGUI($question_gui->getObject()->getId());
        $page_gui->setRenderPageContainer(false);
        $page_gui->setEditPreview(true);
        $page_gui->setEnabledTabs(false);

        $solution_html = $question_gui->getSolutionOutput(
            0,
            null,
            false,
            false,
            true,
            false,
            true,
            false,
            true
        );

        $page_gui->setQuestionHTML([$question_gui->getObject()->getId() => $solution_html]);
        $page_gui->setPresentationTitle($question_gui->getObject()->getTitle());

        $tpl = new ilTemplate('tpl.tst_corrections_solution_presentation.html', true, true, 'components/ILIAS/Test');
        $tpl->setVariable('SOLUTION_PRESENTATION', $page_gui->preview());

        $this->setCorrectionTabsContext($question_gui, 'solution');
        $this->populatePageTitleAndDescription($question_gui);

        $this->main_tpl->setContent($tpl->get());

        $this->main_tpl->setCurrentBlock("ContentStyle");
        $this->main_tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0)
        );
        $this->main_tpl->parseCurrentBlock();

        $this->main_tpl->setCurrentBlock("SyntaxStyle");
        $this->main_tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $this->main_tpl->parseCurrentBlock();
    }

    protected function showAnswerStatistic()
    {
        $question_gui = $this->question_gui;
        $solutions = $this->getSolutions($question_gui->getObject());

        $this->setCorrectionTabsContext($question_gui, 'answers');

        $tablesHtml = '';

        foreach ($question_gui->getSubQuestionsIndex() as $subQuestionIndex) {
            $table = $question_gui->getAnswerFrequencyTableGUI(
                $this,
                'showAnswerStatistic',
                $solutions,
                $subQuestionIndex
            );

            $tablesHtml .= $table->getHTML() . $table->getAdditionalHtml();
        }

        $this->populatePageTitleAndDescription($question_gui);
        $this->main_tpl->setContent($tablesHtml);
    }

    protected function addAnswer()
    {
        $form = (new ilAddAnswerFormBuilder(
            $this->ui_factory,
            $this->refinery,
            $this->language,
            $this->ctrl
        ))->buildAddAnswerModal('')
            ->withRequest($this->testrequest->getRequest());

        $data = $form->getData();

        $question_index = $data['question_index'];
        $answer_value = $data['answer_value'];
        $points = $data['points'];

        if (!$points) {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('err_no_numeric_value'));
            $this->showAnswerStatistic();
            return;
        }

        $question = $this->question_gui->getObject();
        if ($question->isAddableAnswerOptionValue($question_index, $answer_value)) {
            $question->addAnswerOptionValue($question_index, $answer_value, $points);
            $question->saveToDb();
        }

        $scoring = new TestScoring(
            $this->test_obj,
            $this->scorer,
            $this->database,
            $this->language
        );
        $scoring->setPreserveManualScores(true);
        $scoring->recalculateSolutions();

        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logQuestionAdministrationInteraction(
                $question->toQuestionAdministrationInteraction(
                    $this->logger->getAdditionalInformationGenerator(),
                    $this->test_obj->getRefId(),
                    TestQuestionAdministrationInteractionTypes::QUESTION_MODIFIED_IN_CORRECTIONS
                )
            );
        }

        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('saved_successfully'));
        $this->showAnswerStatistic();
    }

    protected function addHiddenItemsFromArray(ilConfirmationGUI $gui, $array, $curPath = [])
    {
        foreach ($array as $name => $value) {
            if ($name == 'cmd' && !count($curPath)) {
                continue;
            }

            if (count($curPath)) {
                $name = "[{$name}]";
            }

            if (is_array($value)) {
                $nextPath = array_merge($curPath, [$name]);
                $this->addHiddenItemsFromArray($gui, $value, $nextPath);
            } else {
                $postVar = implode('', $curPath) . $name;
                $gui->addHiddenItem($postVar, $value);
            }
        }
    }

    protected function setCorrectionTabsContext(assQuestionGUI $question_gui, string $active_tab_id): void
    {
        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();

        $this->help->setScreenIdComponent('tst');
        $this->help->setScreenId('scoringadjust');
        $this->help->setSubScreenId($active_tab_id);

        $this->ctrl->setParameterByClass(self::class, 'q_id', $question_gui->getObject()->getId());
        $this->tabs->addTab(
            'question',
            $this->language->txt('tst_corrections_tab_question'),
            $this->ctrl->getLinkTargetByClass([ilObjTestGUI::class, self::class], 'showQuestion')
        );

        $this->tabs->addTab(
            'solution',
            $this->language->txt('tst_corrections_tab_solution'),
            $this->ctrl->getLinkTargetByClass([ilObjTestGUI::class, self::class], 'showSolution')
        );

        if ($question_gui->isAnswerFrequencyStatisticSupported()) {
            $this->tabs->addTab(
                'answers',
                $this->language->txt('tst_corrections_tab_statistics'),
                $this->ctrl->getLinkTargetByClass([ilObjTestGUI::class, self::class], 'showAnswerStatistic')
            );
        }

        $this->tabs->setBackTarget(
            $this->language->txt('back'),
            $this->ctrl->getLinkTargetByClass(ilObjTestGUI::class, 'showQuestions')
        );

        $this->tabs->activateTab($active_tab_id);
    }

    protected function populatePageTitleAndDescription(assQuestionGUI $question_gui): void
    {
        $this->main_tpl->setTitle($question_gui->getObject()->getTitle());
        $this->main_tpl->setDescription($question_gui->outQuestionType());
    }

    protected function checkQuestion(): bool
    {
        if (!$this->test_obj->isTestQuestion($this->question_gui->getObject()->getId())) {
            return false;
        }

        if (!$this->supportsAdjustment($this->question_gui)) {
            return false;
        }

        return true;
    }

    public function getRefId(): int
    {
        return $this->test_obj->getRefId();
    }

    protected function getQuestionGUI(int $question_id): ?assQuestionGUI
    {
        $question_gui = assQuestion::instantiateQuestionGUI($question_id);
        if ($question_gui === null) {
            return null;
        }
        $question = $question_gui->getObject();
        $question->setPoints($question_gui->getObject()->getMaximumPoints());
        $question_gui->setObject($question);
        return $question_gui;
    }

    protected function getSolutions(assQuestion $question): array
    {
        $solution_rows = [];

        foreach (array_keys($this->test_obj->getParticipants()) as $active_id) {
            $passes_selector = new ilTestPassesSelector($this->database, $this->test_obj);
            $passes_selector->setActiveId($active_id);
            $passes_selector->loadLastFinishedPass();

            foreach ($passes_selector->getClosedPasses() as $pass) {
                foreach ($question->getSolutionValues($active_id, $pass) as $row) {
                    $solution_rows[] = $row;
                }
            }
        }

        return $solution_rows;
    }

    protected function getQuestions(): array
    {

        if (!$this->test_obj->getGlobalSettings()->isAdjustingQuestionsWithResultsAllowed()) {
            return [];
        }

        return array_reduce(
            $this->test_obj->getTestQuestions(),
            function (array $c, array $v): array {
                $question_gui = $this->getQuestionGUI($v['question_id']);

                if (!$this->supportsAdjustment($question_gui)) {
                    return $c;
                }

                $c[] = $v;
                return $c;
            },
            []
        );
    }

    /**
     * Returns if the given question object support scoring adjustment.
     *
     * @param $question_object assQuestionGUI
     *
     * @return bool True, if relevant interfaces are implemented to support scoring adjustment.
     */
    protected function supportsAdjustment(\assQuestionGUI $question_object): bool
    {
        return ($question_object instanceof ilGuiQuestionScoringAdjustable
                || $question_object instanceof ilGuiAnswerScoringAdjustable)
            && ($question_object->getObject() instanceof ilObjQuestionScoringAdjustable
                || $question_object->getObject() instanceof ilObjAnswerScoringAdjustable);
    }
}
