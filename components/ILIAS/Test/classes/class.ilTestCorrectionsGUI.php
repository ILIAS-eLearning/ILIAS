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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Test\RequestDataCollector;
use Psr\Http\Message\RequestInterface;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;

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
    private ilTestAccess $testAccess;

    /**
     * ilTestCorrectionsGUI constructor.
     * @param \ILIAS\DI\Container $DIC
     * @param ilObjTest $test_obj
     */
    public function __construct(
        protected ilDBInterface $database,
        protected ilCtrl $ctrl,
        protected ilAccessHandler $access,
        protected ilLanguage $language,
        protected ilTabsGUI $tabs,
        protected ilHelpGUI $help,
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected ilGlobalTemplateInterface $main_tpl,
        protected RefineryFactory $refinery,
        protected RequestInterface $request,
        private RequestDataCollector $testrequest,
        protected ilObjTest $test_obj,
        protected GeneralQuestionPropertiesRepository $questionrepository,
        protected QuestionsTable $table
    ) {
        $question_id = $this->testrequest->getQuestionId();
        if ($question_id !== 0) {
            $this->question_gui = $this->getQuestionGUI($question_id);
        }
        $this->testAccess = new ilTestAccess($test_obj->getRefId());
    }

    public function executeCommand()
    {
        if (!$this->testAccess->checkCorrectionsAccess()
            || $this->question_gui !== null
                && !$this->checkQuestion()) {
            ilObjTestGUI::accessViolationRedirect();
        }

        if ($this->testrequest->isset('removeQid') && (int) $this->testrequest->raw('removeQid')) {
            $this->confirmQuestionRemoval();
            return;
        }

        $command = $this->ctrl->getCmd('showQuestionList');
        $this->{$command}();
    }

    protected function showQuestionList()
    {
        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_CORRECTION);

        if ($this->testOBJ->isFixedTest()) {
            $rendered_gui_component = $this->ui_renderer->render(
                $this->table
                    ->getTableComponent($this->getQuestions())
                    ->withOrderingDisabled(true)
            );
        } else {
            $txt = $this->language->txt('tst_corrections_incompatible_question_set_type');

            $infoBox = $this->ui_factory->messageBox()->info($txt);

            $rendered_gui_component = $this->ui_renderer->render($infoBox);
        }

        $this->main_tpl->setContent($rendered_gui_component);
    }

    protected function showQuestion(ilPropertyFormGUI $form = null)
    {
        $this->setCorrectionTabsContext($this->question_gui, 'question');

        if ($form === null) {
            $form = $this->buildQuestionCorrectionForm($this->question_gui);
        }

        $this->populatePageTitleAndDescription($this->question_gui);
        $this->main_tpl->setContent($form->getHTML());
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

        $scoring = new TestScoring($this->test_obj, $this->database);
        $scoring->setPreserveManualScores(false);
        $scoring->setQuestionId($question_gui->getObject()->getId());
        $scoring->recalculateSolutions();

        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('saved_successfully'), true);
        $this->ctrl->redirect($this, 'showQuestion');
    }

    protected function buildQuestionCorrectionForm(assQuestionGUI $question_gui): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setId('tst_question_correction');

        $form->setTitle($this->language->txt('tst_corrections_qst_form'));

        $question_gui->populateCorrectionsFormProperties($form);

        $scoring = new TestScoring($this->test_obj, $this->database);
        $scoring->setQuestionId($question_gui->getObject()->getId());

        if ($scoring->getNumManualScorings()) {
            $form->addCommandButton('confirmManualScoringReset', $this->language->txt('save'));
        } else {
            $form->addCommandButton('saveQuestion', $this->language->txt('save'));
        }

        return $form;
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

    protected function confirmManualScoringReset()
    {
        $this->setCorrectionTabsContext($this->question_gui, 'question');

        $scoring = new TestScoring($this->test_obj, $this->database);
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

    protected function showSolution()
    {
        $question_gui = $this->question_gui;
        $page_gui = new ilAssQuestionPageGUI($question_gui->getObject()->getId());
        $page_gui->setRenderPageContainer(false);
        $page_gui->setEditPreview(true);
        $page_gui->setEnabledTabs(false);

        $solutionHTML = $question_gui->getSolutionOutput(
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

        $page_gui->setQuestionHTML([$question_gui->getObject()->getId() => $solutionHTML]);
        $page_gui->setPresentationTitle($question_gui->getObject()->getTitle());

        $tpl = new ilTemplate('tpl.tst_corrections_solution_presentation.html', true, true, 'components/ILIAS/Test');
        $tpl->setVariable('SOLUTION_PRESENTATION', $page_gui->preview());

        $this->setCorrectionTabsContext($question_gui, 'solution');
        $this->populatePageTitleAndDescription($question_gui);

        $this->main_tpl->setContent($tpl->get());

        $this->main_tpl->setCurrentBlock("ContentStyle");
        $stylesheet = ilObjStyleSheet::getContentStylePath(0);
        $this->main_tpl->setVariable("LOCATION_CONTENT_STYLESHEET", $stylesheet);
        $this->main_tpl->parseCurrentBlock();

        $this->main_tpl->setCurrentBlock("SyntaxStyle");
        $stylesheet = ilObjStyleSheet::getSyntaxStylePath();
        $this->main_tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", $stylesheet);
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
        $form_builder = new ilAddAnswerFormBuilder($this, $this->ui_factory, $this->refinery, $this->language, $this->ctrl);

        $form = $form_builder->buildAddAnswerForm()
            ->withRequest($this->request);

        $data = $form->getData();

        $question_index = $data['question_index'];
        $answer_value = $data['answer_value'];
        $points = $data['points'];

        if (!$points) {
            $this->main_tpl->setOnScreenMessage('failure', $this->language->txt('err_no_numeric_value'));
            $this->showAnswerStatistic();
            return;
        }

        if ($this->question_gui->getObject()->isAddableAnswerOptionValue($question_index, $answer_value)) {
            $this->question_gui->getObject()->addAnswerOptionValue($question_index, $answer_value, $points);
            $this->question_gui->getObject()->saveToDb();
        }

        $scoring = new TestScoring($this->test_obj, $this->database);
        $scoring->setPreserveManualScores(true);
        $scoring->recalculateSolutions();

        $this->main_tpl->setOnScreenMessage('success', $this->language->txt('saved_successfully'));
        $this->showAnswerStatistic();
    }

    protected function confirmQuestionRemoval()
    {
        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_CORRECTION);

        $confirmation = sprintf(
            $this->language->txt('tst_corrections_qst_remove_confirmation'),
            $this->question_gui->getObject()->getTitle(),
            $this->question_gui->getObject()->getId()
        );

        $buttons = [
            $this->ui_factory->button()->standard(
                $this->language->txt('confirm'),
                $this->ctrl->getLinkTarget($this, 'performQuestionRemoval')
            ),
            $this->ui_factory->button()->standard(
                $this->language->txt('cancel'),
                $this->ctrl->getLinkTarget($this, 'showQuestionList')
            )
        ];

        $this->main_tpl->setContent($this->ui_renderer->render(
            $this->ui_factory->messageBox()->confirmation($confirmation)->withButtons($buttons)
        ));
    }

    protected function performQuestionRemoval(): void
    {
        $question_gui = $this->question_gui;
        $scoring = new TestScoring($this->test_obj, $this->database);

        $participant_data = new ilTestParticipantData($this->database, $this->language);
        $participant_data->load($this->test_obj->getTestId());

        // remove question solutions
        $question_gui->getObject()->removeAllExistingSolutions();

        // remove test question results
        $scoring->removeAllQuestionResults($question_gui->getObject()->getId());

        // remove question from test and reindex remaining questions
        $this->test_obj->removeQuestion($question_gui->getObject()->getId());
        $reindexedSequencePositionMap = $this->test_obj->reindexFixedQuestionOrdering();
        $this->test_obj->loadQuestions();

        // remove questions from all sequences
        $this->test_obj->removeQuestionFromSequences(
            $question_gui->getObject()->getId(),
            $participant_data->getActiveIds(),
            $reindexedSequencePositionMap
        );

        // update pass and test results
        $scoring->updatePassAndTestResults($participant_data->getActiveIds());

        // trigger learning progress
        ilLPStatusWrapper::_refreshStatus($this->test_obj->getId(), $participant_data->getUserIds());

        // finally delete the question itself
        $question_gui->getObject()->delete($question_gui->getObject()->getId());

        // check for empty test and set test offline
        if (!count($this->test_obj->getTestQuestions())) {
            $object_properties = $this->test_obj->getObjectProperties();
            $object_properties->storePropertyIsOnline(
                $object_properties->getPropertyIsOnline()->withOffline()
            );
        }

        $this->

        $this->question_gui = null;

        $this->ctrl->clearParameterByClass(self::class, 'q_id');
        $this->showQuestionList();
    }

    protected function setCorrectionTabsContext(assQuestionGUI $question_gui, string $active_tab_id): void
    {
        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();

        $this->help->setScreenIdComponent("tst");
        $this->help->setScreenId("scoringadjust");
        $this->help->setSubScreenId($active_tab_id);


        $this->tabs->setBackTarget(
            $this->language->txt('back'),
            $this->ctrl->getLinkTarget($this, 'showQuestionList')
        );

        $this->tabs->addTab(
            'question',
            $this->language->txt('tst_corrections_tab_question'),
            $this->ctrl->getLinkTarget($this, 'showQuestion')
        );

        $this->tabs->addTab(
            'solution',
            $this->language->txt('tst_corrections_tab_solution'),
            $this->ctrl->getLinkTarget($this, 'showSolution')
        );

        if ($question_gui->isAnswerFrequencyStatisticSupported()) {
            $this->tabs->addTab(
                'answers',
                $this->language->txt('tst_corrections_tab_statistics'),
                $this->ctrl->getLinkTarget($this, 'showAnswerStatistic')
            );
        }

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

        if (!$this->allowedInAdjustment($this->question_gui)) {
            return false;
        }

        return true;
    }

    public function getRefId(): int
    {
        return $this->test_obj->getRefId();
    }

    protected function getQuestionGUI(int $question_id): assQuestionGUI
    {
        $question_gui = assQuestion::instantiateQuestionGUI($question_id);
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

    /**
     * @return array
     */
    protected function getQuestions(): array
    {
        $questions = [];

        foreach ($this->test_obj->getTestQuestions() as $question_data) {
            $question_gui = $this->getQuestionGUI($question_data['question_id']);

            if (!$this->supportsAdjustment($question_gui)) {
                continue;
            }

            if (!$this->allowedInAdjustment($question_gui)) {
                continue;
            }

            $questions[] = $question_data;
        }

        return $questions;
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

    /**
     * Returns if the question type is allowed for adjustments in the global test administration.
     *
     * @param assQuestionGUI $question_object
     * @return bool
     */
    protected function allowedInAdjustment(\assQuestionGUI $question_object): bool
    {
        $setting = new ilSetting('assessment');
        $types = explode(',', $setting->get('assessment_scoring_adjustment'));
        $type_def = [];
        foreach ($types as $type) {
            $type_def[$type] = ilObjQuestionPool::getQuestionTypeByTypeId($type);
        }

        $type = $question_object->getQuestionType();
        if (in_array($type, $type_def)) {
            return true;
        }
        return false;
    }
}
