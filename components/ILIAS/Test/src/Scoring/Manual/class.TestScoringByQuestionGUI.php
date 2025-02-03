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

namespace ILIAS\Test\Scoring\Manual;

use ILIAS\Test\Presentation\TabsManager;
use ILIAS\Test\Logging\TestScoringInteractionTypes;
use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Test\Questions\Properties\Properties as TestQuestionProperties;
use ILIAS\UI\Component\Link\Standard as StandardLink;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\Factory as DataFactory;

class TestScoringByQuestionGUI extends TestScoringByParticipantGUI
{
    private const CMD_SHOW = 'showManScoringByQuestionParticipantsTable';
    private const CMD_SAVE = 'saveManScoringByQuestion';

    private URLBuilder $url_builder;
    private URLBuilderToken $action_parameter_token;
    private URLBuilderToken $row_id_token;

    public function __construct(
        \ilObjTest $object,
        private readonly \ilUIService $ui_service
    ) {
        parent::__construct($object);
        $this->lng->loadLanguageModule('form');

        $this->ctrl->saveParameterByClass(self::class, 'q_id');
        $uri = ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
            [\ilObjTestGUI::class, self::class],
            $this->getDefaultCommand()
        );
        $this->ctrl->clearParameterByClass(self::class, 'q_id');
        $url_builder = new URLBuilder(
            (new DataFactory())->uri($uri)
        );

        list(
            $this->url_builder,
            $this->action_parameter_token,
            $this->row_id_token
        ) = $url_builder->acquireParameters(
            ['manual_scoring', 'by_question'],
            'action',
            'row'
        );
    }

    protected function getDefaultCommand(): string
    {
        return self::CMD_SHOW;
    }

    protected function getActiveSubTabId(): string
    {
        return 'man_scoring_by_qst';
    }

    protected function showManScoringByQuestionParticipantsTable(
        ?RoundTripModal $modal = null
    ): void {
        $this->tabs->activateTab(TabsManager::TAB_ID_MANUAL_SCORING);

        $this->initJavascript();

        if (!$this->test_access->checkScoreParticipantsAccess()
            || !$this->object->getGlobalSettings()->isManualScoringEnabled()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_test'), true);
            $this->ctrl->redirectByClass([\ilRepositoryGUI::class, \ilObjTestGUI::class, \ilInfoScreenGUI::class]);
        }

        $test_question_properties = $this->testquestionsrepository
            ->getQuestionPropertiesForTest($this->object);

        if ($test_question_properties === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('manscoring_questions_not_found'));
            return;
        }

        $this->toolbar->addComponent(
            $this->ui_factory->dropdown()->standard(
                $this->buildSelectableQuestionsArray($test_question_properties)
            )->withLabel(
                $this->lng->txt('select_question')
            )
        );

        $question_id = $this->testrequest->getQuestionId();
        if ($question_id === 0) {
            $question_id = reset($test_question_properties)->getQuestionId();
        }

        $table = new ScoringByQuestionTable(
            $this->lng,
            $this->url_builder,
            $this->action_parameter_token,
            $this->row_id_token,
            $this->ui_factory
        );

        if ($this->testrequest->strVal($this->action_parameter_token->getName()) === ScoringByQuestionTable::ACTION_SCORING) {
            $affected_rows = $this->testrequest->raw($this->row_id_token->getName());
            $this->getAnswerDetail($question_id, $affected_rows[0]);
        }

        $content = [
            $table->getTable(
                $this->buildQuestionTitleWithPoints($test_question_properties[$question_id]),
                $this->user->getDateTimeFormat(),
                $this->http->request(),
                $this->ui_service,
                $this->ctrl->getLinkTargetByClass(
                    [\ilObjTestGUI::class, self::class],
                    $this->getDefaultCommand()
                ),
                new ScoringByQuestionTableBinder(
                    $this->lng,
                    new \DateTimeZone($this->user->getTimeZone()),
                    $this->participant_access_filter,
                    $this->object,
                    $question_id
                )
            )
        ];

        if ($modal !== null) {
            $content[] = $modal->withOnLoad($modal->getShowSignal());
        }

        $this->tpl->setContent($this->ui_renderer->render($content));
    }

    protected function saveManScoringByQuestion(): void
    {
        $active_id = $this->testrequest->getActiveId();
        $question_id = $this->testrequest->getQuestionId();
        $attempt = $this->testrequest->getPassId();
        if ($active_id === 0 || $question_id === 0
            || !$this->test_access->checkScoreParticipantsAccessForActiveId($active_id, $this->object->getTestId())) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('cannot_edit_test'), true);
            $this->ctrl->redirectByClass(\ilObjTestGUI::class);
        }

        $question_gui = $this->object->createQuestionGUI('', $question_id);
        $previously_reached_points = $question_gui->getObject()->getReachedPoints($active_id, $attempt);
        $available_points = $question_gui->getObject()->getMaximumPoints();
        $feedback = \ilObjTest::getSingleManualFeedback($active_id, $question_id, $attempt);
        $form = $this->buildForm(
            $attempt,
            $active_id,
            $question_id,
            $previously_reached_points,
            $available_points
        );

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->showManScoringByQuestionParticipantsTable(
                $this->buildFeedbackModal(
                    $question_id,
                    $active_id,
                    $attempt,
                    $form
                )
            );
            return;
        }

        if (isset($feedback['finalized_evaluation'])
            && $feedback['finalized_evaluation'] === 1) {
            $new_reached_points = $previously_reached_points;
            $feedback_text = $feedback['feedback'];
        } else {
            $new_reached_points = $this->refinery->kindlyTo()->float()
                ->transform($form->getInput('points'));
            $feedback_text = \ilUtil::stripSlashes(
                $form->getInput('feedback'),
                false,
                \ilObjAdvancedEditing::_getUsedHTMLTagsAsString('assessment')
            );
        }
        if ($new_reached_points !== $previously_reached_points) {
            \assQuestion::_setReachedPoints(
                $active_id,
                $question_id,
                $new_reached_points,
                $available_points,
                $attempt,
                true
            );
            \ilLPStatusWrapper::_updateStatus(
                $this->object->getId(),
                \ilObjTestAccess::_getParticipantId($active_id)
            );
        }

        $finalized = $this->refinery->byTrying([
            $this->refinery->kindlyTo()->bool(),
            $this->refinery->always(false)
        ])->transform($form->getInput('finalized'));
        $this->object->saveManualFeedback(
            $active_id,
            $question_id,
            $attempt,
            $feedback_text,
            $finalized
        );

        if ($this->logger->isLoggingEnabled()) {
            $this->logger->logScoringInteraction(
                $this->logger->getInteractionFactory()->buildScoringInteraction(
                    $this->getObject()->getRefId(),
                    $question_id,
                    $this->user->getId(),
                    \ilObjTestAccess::_getParticipantId($active_id),
                    TestScoringInteractionTypes::QUESTION_GRADED,
                    [
                        AdditionalInformationGenerator::KEY_REACHED_POINTS => $new_reached_points,
                        AdditionalInformationGenerator::KEY_FEEDBACK => $feedback_text,
                        AdditionalInformationGenerator::KEY_EVAL_FINALIZED => $this->logger
                            ->getAdditionalInformationGenerator()->getTrueFalseTagForBool($finalized)
                    ]
                )
            );
        }

        $this->tpl->setOnScreenMessage(
            'success',
            sprintf(
                $this->lng->txt('tst_saved_manscoring_by_question_successfully'),
                $question_gui->getObject()->getTitle(),
                $attempt + 1
            )
        );
        $this->showManScoringByQuestionParticipantsTable();
    }

    protected function getAnswerDetail(int $question_id, string $row_id): void
    {
        $row_info_array = explode('_', $row_id);

        if (count($row_info_array) !== 2) {
            $this->http->close();
        }

        [$active_id, $attempt] = $this->refinery->container()->mapValues(
            $this->refinery->kindlyTo()->int()
        )->transform($row_info_array);

        if (!$this->getTestAccess()->checkScoreParticipantsAccessForActiveId($active_id, $this->object->getTestId())) {
            $this->http->close();
        }

        $this->http->saveResponse(
            $this->http->response()->withBody(
                \ILIAS\Filesystem\Stream\Streams::ofString(
                    $this->ui_renderer->renderAsync(
                        $this->buildFeedbackModal($question_id, $active_id, $attempt)
                    )
                )
            )->withHeader(\ILIAS\HTTP\Response\ResponseHeader::CONTENT_TYPE, 'text/html')
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    private function buildFeedbackModal(
        int $question_id,
        int $active_id,
        int $attempt,
        ?\ilPropertyFormGUI $form = null
    ): RoundTripModal {
        $question_gui = $this->object->createQuestionGUI('', $question_id);

        $content = [
            $this->buildSolutionPanel($question_gui, $question_id, $attempt)
        ];

        if ($question_gui instanceof \assTextQuestionGUI && $this->object->getAutosave()) {
            $content[] = $this->buildAutosavedSolutionPanel($question_gui, $question_id, $attempt);
        }

        $reached_points = $question_gui->getObject()->getReachedPoints($active_id, $attempt);
        $available_points = $question_gui->getObject()->getMaximumPoints();
        $content[] = $this->ui_factory->panel()->standard(
            $this->lng->txt('scoring'),
            $this->ui_factory->legacy()->content(
                sprintf(
                    $this->lng->txt('part_received_a_of_b_points'),
                    $reached_points,
                    $available_points
                )
            )
        );

        $suggested_solution = \assQuestion::_getSuggestedSolutionOutput($question_id);
        if ($this->object->getShowSolutionSuggested() && $suggested_solution !== '') {
            $content[] = $this->ui_factory->legacy()->content(
                $this->ui_factory->panel()->standard(
                    $this->lng->txt('solution_hint'),
                    $suggested_solution
                )
            );
        }

        $content[] = $this->ui_factory->legacy()->content(($form ?? $this->buildForm(
            $attempt,
            $active_id,
            $question_id,
            $reached_points,
            $available_points
        ))->getHTMLAsync());

        return $this->ui_factory->modal()->roundtrip(
            $this->getModalTitle($active_id),
            $content
        );
    }

    private function buildSolutionPanel(
        \assQuestionGUI $question_gui,
        int $active_id,
        int $attempt
    ): StandardPanel {
        return $this->ui_factory->panel()->standard(
            $question_gui->getObject()->getTitle(),
            $this->ui_factory->legacy()->content(
                $question_gui->getSolutionOutput(
                    $active_id,
                    $attempt,
                    false,
                    false,
                    false,
                    $this->object->getShowSolutionFeedback(),
                )
            )
        );
    }

    private function buildAutosavedSolutionPanel(
        assQuestionGUI $question_gui,
        int $active_id,
        int $attempt
    ): StandardPanel {
        return $this->ui_factory->panel()->standard(
            $this->lng->txt('autosavecontent'),
            $this->ui_factory->legacy()->content(
                $question_gui->getAutoSavedSolutionOutput(
                    $active_id,
                    $attempt,
                    false,
                    false,
                    false,
                    $this->object->getShowSolutionFeedback(),
                )
            )
        );
    }

    private function getModalTitle(int $active_id): string
    {
        if ($this->object->getAnonymity() === true) {
            return $this->lng->txt('answers_of') . ' ' . $this->lng->txt('anonymous');
        }
        return $this->lng->txt('answers_of') . ' ' . $this->object->getCompleteEvaluationData()
            ->getParticipant($active_id)
            ->getName();
    }

    private function buildForm(
        int $attempt,
        int $active_id,
        int $question_id,
        float $reached_points,
        float $available_points
    ): \ilPropertyFormGUI {
        $feedback = \ilObjTest::getSingleManualFeedback($active_id, $question_id, $attempt);
        $finalized = isset($feedback['finalized_evaluation'])
            && $feedback['finalized_evaluation'] === 1;

        $form = new \ilPropertyFormGUI();
        $form->setFormAction($this->buildFormTarget($question_id, $active_id, $attempt));
        $form->setTitle($this->lng->txt('manscoring'));
        $form->addCommandButton(self::CMD_SAVE, $this->lng->txt('save'));
        $form->setId('fb');

        if ($finalized) {
            $feedback_input = new \ilNonEditableValueGUI(
                $this->lng->txt('set_manual_feedback'),
                'feedback',
                true
            );
        } else {
            $feedback_input = new \ilTextAreaInputGUI(
                $this->lng->txt('set_manual_feedback'),
                'feedback'
            );
            $feedback_input->setUseRte(true);
        }
        $feedback_input->setValue($feedback['feedback'] ?? '');
        $form->addItem($feedback_input);

        $reached_points_input = new \ilNumberInputGUI(
            $this->lng->txt('tst_change_points_for_question'),
            'points'
        );
        $reached_points_input->allowDecimals(true);
        $reached_points_input->setSize(5);
        $reached_points_input->setMaxValue($available_points, true);
        $reached_points_input->setMinValue(0);
        $reached_points_input->setDisabled($finalized);
        $reached_points_input->setValue((string) $reached_points);
        $reached_points_input->setClientSideValidation(true);
        $form->addItem($reached_points_input);

        $finalized_input = new \ilCheckboxInputGUI(
            $this->lng->txt('finalized_evaluation'),
            'finalized'
        );
        $finalized_input->setChecked($finalized);
        $form->addItem($finalized_input);

        return $form;
    }

    protected function buildFormTarget(
        int $question_id,
        int $active_id,
        int $attempt
    ): string {
        $this->ctrl->setParameterByClass(self::class, 'q_id', $question_id);
        $this->ctrl->setParameterByClass(self::class, 'active_id', $active_id);
        $this->ctrl->setParameterByClass(self::class, 'pass_id', $attempt);
        $target = $this->ctrl->getFormAction($this, self::CMD_SAVE);
        $this->ctrl->clearParameterByClass(self::class, 'q_id');
        $this->ctrl->clearParameterByClass(self::class, 'active_id');
        $this->ctrl->clearParameterByClass(self::class, 'pass_id');
        return $target;
    }

    /**
     *
     * @param array<ILIAS\Test\Questions\Properties\Properties> $question_data
     * @return array<ILIAS\UI\Component\Link\Standard>
     */
    private function buildSelectableQuestionsArray(array $question_data): array
    {
        $dropdown = array_map(
            function (TestQuestionProperties $v): StandardLink {
                $this->ctrl->setParameterByClass(self::class, 'q_id', $v->getGeneralQuestionProperties()->getQuestionId());
                return $this->ui_factory->link()->standard(
                    $this->buildQuestionTitleWithPoints($v),
                    $this->ctrl->getLinkTargetByClass(self::class, $this->getDefaultCommand())
                );
            },
            $question_data
        );
        $this->ctrl->clearParameterByClass(self::class, 'q_id');
        return $dropdown;
    }

    private function buildQuestionTitleWithPoints(TestQuestionProperties $test_question_properties): string
    {
        $question_properties = $test_question_properties->getGeneralQuestionProperties();
        $lang_var = $question_properties->getAvailablePoints() === 1.0 ? $this->lng->txt('point') : $this->lng->txt('points');
        return "{$question_properties->getTitle()} ({$question_properties->getAvailablePoints()} {$lang_var}) "
                    . "[{$this->lng->txt('question_id_short')}: {$question_properties->getQuestionId()}]";
    }

    private function initJavascript(): void
    {
        $math_jax_setting = new \ilSetting('MathJax');
        if ($math_jax_setting->get('enable')) {
            $this->tpl->addJavaScript($math_jax_setting->get('path_to_mathjax'));
        }

        if (\ilObjAdvancedEditing::_getRichTextEditor() === 'tinymce') {
            $this->initTinymce();
        }
    }

    private function initTinymce(): void
    {
        $this->tpl->addJavaScript('node_modules/tinymce/tinymce.min.js');
        $this->tpl->addOnLoadCode("
            const aO = (o) => {
                o.observe(
                    document.getElementById('ilContentContainer'),
                    {childList: true, subtree: true}
                );
            }
            const o = new MutationObserver(
                (ml, o) => {
                    o.disconnect();
                    tinymce.remove();
                    tinymce.init({
                        selector: 'textarea.RTEditor',
                        branding: false,
                        height: 250,
                        fix_list_elements: true,
                        statusbar: false,
                        menubar: false,
                        plugins: 'lists',
                        toolbar: 'bold italic underline strikethrough | undo redo | bullist numlist',
                        toolbar_mode: 'sliding',
                        init_instance_callback: () => {aO(o);}
                    });
                }
            );
            aO(o);
        ");
    }
}
