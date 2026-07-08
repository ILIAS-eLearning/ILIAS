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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Skill\Service\SkillUsageService;
use ILIAS\TestQuestionPool\RequestDataCollectorInterface;
use ILIAS\TestQuestionPool\Skills\SkillsByQuestionOverviewTable;
use ILIAS\TestQuestionPool\Skills\EditSkillsOfQuestionTable;
use ILIAS\TestQuestionPool\Skills\EditSkillsOfQuestionTableActions;
use ILIAS\TestQuestionPool\Skills\EditSkillsOfQuestionTableEditAction;
use ILIAS\TestQuestionPool\Skills\SkillAssignmentViewControlMode;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\URLBuilder;

/**
 * User interface for assignment of questions from a test question pool (or
 * directly from a test) to competences.
 *
 * @author  Björn Heyser <bheyser@databay.de>
 * @package components\ILIAS/Test
 *
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilSkillSelectorGUI
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilToolbarGUI
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilAssQuestionSkillAssignmentPropertyFormGUI
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilAssQuestionPageGUI
 * @ilCtrl_Calls ilAssQuestionSkillAssignmentsGUI: ilConfirmationGUI
 */
class ilAssQuestionSkillAssignmentsGUI
{
    public const CMD_SHOW_SKILL_QUEST_ASSIGNS = 'showSkillQuestionAssignments';
    public const CMD_EDIT_SKILL_QUEST_ASSIGNS = 'editSkillQuestionAssignment';
    public const CMD_SHOW_SKILL_SELECT = 'showSkillSelection';
    public const CMD_UPDATE_SKILL_QUEST_ASSIGNS = 'updateSkillQuestionAssignments';
    public const CMD_SHOW_SKILL_QUEST_ASSIGN_PROPERTIES_FORM = 'showSkillQuestionAssignmentPropertiesForm';
    public const CMD_SAVE_SKILL_QUEST_ASSIGN_PROPERTIES_FORM = 'saveSkillQuestionAssignmentPropertiesForm';
    public const CMD_SAVE_SKILL_POINTS = 'saveSkillPoints';
    public const CMD_SHOW_SYNC_ORIGINAL_CONFIRMATION = 'showSyncOriginalConfirmation';
    public const CMD_SYNC_ORIGINAL = 'syncOriginal';

    public const PARAM_SKILL_SELECTION = 'skill_ids';

    private ilAssQuestionList $question_list;
    private int $question_container_id;
    private bool $assignment_editing_enabled;
    private ?string $assignment_configuration_hint_message = null;

    private array $questionOrderSequence;

    private DataFactory $data_factory;

    public function __construct(
        private readonly ilCtrl $ctrl,
        private readonly ilAccessHandler $access,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilLanguage $lng,
        private readonly ilDBInterface $db,
        private readonly RequestDataCollectorInterface $request_data_collector,
        private readonly SkillUsageService $skill_usage_service,
        private readonly UIFactory $ui_factory,
        private readonly UIRenderer $ui_renderer,
        private readonly Refinery $refinery,
        private readonly HTTP $http,
        private readonly ilToolbarGUI $toolbar,
        private readonly ilTabsGUI $tabs,
        private readonly ?ilObjTest $test_object = null
    ) {
        $this->data_factory = new DataFactory();
    }

    public function getQuestionOrderSequence(): ?array
    {
        return $this->questionOrderSequence;
    }

    public function getAssignmentConfigurationHintMessage(): ?string
    {
        return $this->assignment_configuration_hint_message;
    }

    public function setAssignmentConfigurationHintMessage(?string $assignmentConfigurationHintMessage): void
    {
        $this->assignment_configuration_hint_message = $assignmentConfigurationHintMessage;
    }

    /**
     * @param array $questionOrderSequence
     */
    public function setQuestionOrderSequence($questionOrderSequence): void
    {
        $this->questionOrderSequence = $questionOrderSequence;
    }

    /**
     * @return ilAssQuestionList
     */
    public function getQuestionList(): ilAssQuestionList
    {
        return $this->question_list;
    }

    /**
     * @param ilAssQuestionList $questionList
     */
    public function setQuestionList($questionList): void
    {
        $this->question_list = $questionList;
    }

    /**
     * @return int
     */
    public function getQuestionContainerId(): int
    {
        return $this->question_container_id;
    }

    /**
     * @param int $questionContainerId
     */
    public function setQuestionContainerId($questionContainerId): void
    {
        $this->question_container_id = $questionContainerId;
    }

    /**
     * @return bool
     */
    public function isAssignmentEditingEnabled(): bool
    {
        return $this->assignment_editing_enabled;
    }

    /**
     * @param bool $assignmentEditingEnabled
     */
    public function setAssignmentEditingEnabled($assignmentEditingEnabled): void
    {
        $this->assignment_editing_enabled = $assignmentEditingEnabled;
    }

    public function executeCommand(): void
    {
        $nextClass = $this->ctrl->getNextClass();

        $command = $this->ctrl->getCmd(self::CMD_SHOW_SKILL_QUEST_ASSIGNS);

        if ($this->isAvoidManipulationRedirectRequired($command)) {
            $this->ctrl->redirect($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
        }

        $this->keepAssignmentParameters();

        if (in_array($command, [self::CMD_EDIT_SKILL_QUEST_ASSIGNS])) {
            $this->modifyTabs();
        }

        switch ($nextClass) {
            case strtolower(__CLASS__):
            case '':

                $command .= 'Cmd';
                $this->$command();
                break;

            default:

                throw new ilTestQuestionPoolException('unsupported next class in ctrl flow');
        }
    }

    private function modifyTabs(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget(
            $this->lng->txt('back'),
            $this->ctrl->getLinkTargetByClass(
                ilAssQuestionSkillAssignmentsGUI::class,
                ilAssQuestionSkillAssignmentsGUI::CMD_SHOW_SKILL_QUEST_ASSIGNS
            )
        );
        $this->tabs->addTab(
            strtolower(ilAssQuestionSkillAssignmentsGUI::class),
            $this->lng->txt('qpl_skl_sub_tab_quest_assign'),
            $this->ctrl->getLinkTargetByClass(
                ilAssQuestionSkillAssignmentsGUI::class,
                ilAssQuestionSkillAssignmentsGUI::CMD_EDIT_SKILL_QUEST_ASSIGNS
            )
        );
        $this->tabs->activateTab(strtolower(ilAssQuestionSkillAssignmentsGUI::class));
    }

    private function isAvoidManipulationRedirectRequired($command): bool
    {
        if ($this->isAssignmentEditingEnabled()) {
            return false;
        }

        switch ($command) {
            case self::CMD_SAVE_SKILL_QUEST_ASSIGN_PROPERTIES_FORM:
            case self::CMD_UPDATE_SKILL_QUEST_ASSIGNS:

                return true;
        }

        return false;
    }

    private function saveSkillPointsCmd(): void
    {
        $success = true;
        $skill_points = $this->request_data_collector->raw('skill_points');

        for ($i = 0; $i < 2; $i++) {
            foreach ($skill_points as $assignment_key => $skill_point) {
                $assignment_key = explode(':', $assignment_key);
                $skillBaseId = (int) $assignment_key[0];
                $skillTrefId = (int) $assignment_key[1];
                $questionId = (int) $assignment_key[2];

                if ($this->isTestQuestion($questionId)) {
                    $assignment = new ilAssQuestionSkillAssignment($this->db);

                    if ($i === 0) {
                        if (!$assignment->isValidSkillPoint($skill_point)) {
                            $success = false;
                            break 2;
                        }
                        continue;
                    }

                    $assignment->setParentObjId($this->getQuestionContainerId());
                    $assignment->setQuestionId($questionId);
                    $assignment->setSkillBaseId($skillBaseId);
                    $assignment->setSkillTrefId($skillTrefId);

                    if ($assignment->dbRecordExists()) {
                        $assignment->loadFromDb();

                        if (!$assignment->hasEvalModeBySolution()) {
                            $assignment->setSkillPoints($skill_point);
                            $assignment->saveToDb();

                            // add skill usage
                            $this->skill_usage_service->addUsage($this->getQuestionContainerId(), $skillBaseId, $skillTrefId);
                        }
                    }
                }

            }
        }

        if ($success) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_msg_skl_qst_assign_points_saved'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_SKILL_QUEST_ASSIGNS);
            return;
        }

        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_msg_skl_qst_assign_points_not_saved'));
        $this->showSkillQuestionAssignmentsCmd(true);
    }

    private function updateSkillQuestionAssignmentsCmd(): void
    {
        $this->keepAssignmentParameters();
        $question_id = $this->request_data_collector->getQuestionId();

        if ($this->isTestQuestion($question_id)) {
            $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
            $assignmentList->setParentObjId($this->getQuestionContainerId());
            $assignmentList->loadFromDb();

            $handledSkills = [];

            $sgui = $this->buildSkillSelectorExplorerGUI([]);
            $skillIds = $sgui->getSelectedSkills();

            foreach ($skillIds as $skillId) {
                $skill = explode(':', $skillId);
                $skillBaseId = (int) $skill[0];
                $skillTrefId = (int) $skill[1];

                if ($skillBaseId) {
                    if (!$assignmentList->isAssignedToQuestionId($skillBaseId, $skillTrefId, $question_id)) {
                        $assignment = new ilAssQuestionSkillAssignment($this->db);

                        $assignment->setParentObjId($this->getQuestionContainerId());
                        $assignment->setQuestionId($question_id);
                        $assignment->setSkillBaseId($skillBaseId);
                        $assignment->setSkillTrefId($skillTrefId);

                        $assignment->setSkillPoints(ilAssQuestionSkillAssignment::DEFAULT_COMPETENCE_POINTS);
                        $assignment->setEvalMode(ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT);
                        $assignment->saveToDb();

                        // add skill usage
                        $this->skill_usage_service->addUsage($this->getQuestionContainerId(), $skillBaseId, $skillTrefId);
                    }

                    $handledSkills[$skillId] = $skill;
                }
            }

            foreach ($assignmentList->getAssignmentsByQuestionId($question_id) as $assignment) {
                if (isset($handledSkills["{$assignment->getSkillBaseId()}:{$assignment->getSkillTrefId()}"])) {
                    continue;
                }

                $assignment->deleteFromDb();

                // remove skill usage
                if (!$assignment->isSkillUsed()) {
                    $this->skill_usage_service->removeUsage(
                        $assignment->getParentObjId(),
                        $assignment->getSkillBaseId(),
                        $assignment->getSkillTrefId()
                    );
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('qpl_qst_skl_assigns_updated'), true);

            if ($this->isSyncOriginalPossibleAndAllowed($question_id)) {
                $this->keepAssignmentParameters();
                $this->ctrl->redirect($this, self::CMD_SHOW_SYNC_ORIGINAL_CONFIRMATION);
            }
        }

        $this->editSkillQuestionAssignmentCmd();
    }

    private function showSkillSelectionCmd(): void
    {
        $this->keepAssignmentParameters();
        $question_id = $this->request_data_collector->getQuestionId();

        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($this->getQuestionContainerId());
        $assignmentList->loadFromDb();

        $skillSelectorExplorerGUI = $this->buildSkillSelectorExplorerGUI(
            $assignmentList->getAssignmentsByQuestionId($question_id)
        );

        if (!$skillSelectorExplorerGUI->handleCommand()) {
            $tpl = new ilTemplate('tpl.qpl_qst_skl_assign_selection.html', false, false, 'components/ILIAS/TestQuestionPool');

            $tpl->setVariable('SKILL_SELECTOR_HEADER', $this->getSkillSelectorHeader($question_id));

            $skillSelectorToolbarGUI = $this->buildSkillSelectorToolbarGUI();

            $skillSelectorToolbarGUI->setOpenFormTag(true);
            $skillSelectorToolbarGUI->setCloseFormTag(false);
            $skillSelectorToolbarGUI->setLeadingImage(ilUtil::getImagePath("nav/arrow_upright.svg"), " ");
            $tpl->setVariable('SKILL_SELECTOR_TOOLBAR_TOP', $this->ctrl->getHTML($skillSelectorToolbarGUI));

            $tpl->setVariable('SKILL_SELECTOR_EXPLORER', $this->ctrl->getHTML($skillSelectorExplorerGUI));

            $skillSelectorToolbarGUI->setOpenFormTag(false);
            $skillSelectorToolbarGUI->setCloseFormTag(true);
            $skillSelectorToolbarGUI->setLeadingImage(ilUtil::getImagePath("nav/arrow_downright.svg"), " ");
            $tpl->setVariable('SKILL_SELECTOR_TOOLBAR_BOTTOM', $this->ctrl->getHTML($skillSelectorToolbarGUI));

            $this->tpl->setContent($tpl->get());
        }
    }

    private function showSkillQuestionAssignmentPropertiesFormCmd(
        ?assQuestionGUI $question_gui = null,
        ?ilAssQuestionSkillAssignment $assignment = null,
        ?ilPropertyFormGUI $form = null
    ): void {
        $this->handleAssignmentConfigurationHintMessage();

        $this->keepAssignmentParameters();

        $question_id = $this->request_data_collector->getQuestionId();
        $question_gui ??= assQuestionGUI::_getQuestionGUI('', $question_id);

        $row_id_parameter = $this->request_data_collector->getRowIdParameter(EditSkillsOfQuestionTableActions::FULL_ROW_ID_PARAMETER);
        [1 => $skill_base_id, 2 => $skill_tref_id] = explode('_', $row_id_parameter);
        $assignment ??= $this->buildQuestionSkillAssignment($question_id, (int) $skill_base_id, (int) $skill_tref_id);

        $form ??= $this->buildSkillQuestionAssignmentPropertiesForm($question_gui->getObject(), $assignment);

        $this->tpl->setContent($form->getHTML() . '<br />' . $this->buildQuestionPage($question_gui));
    }

    private function saveSkillQuestionAssignmentPropertiesFormCmd(): void
    {
        $question_id = $this->request_data_collector->getQuestionId();

        if ($this->isTestQuestion($question_id)) {
            $question_gui = assQuestionGUI::_getQuestionGUI('', $question_id);

            $row_id_parameter = $this->request_data_collector->getRowIdParameter(EditSkillsOfQuestionTableActions::FULL_ROW_ID_PARAMETER);
            [1 => $skill_base_id, 2 => $skill_tref_id] = explode('_', $row_id_parameter);
            $assignment = $this->buildQuestionSkillAssignment($question_id, (int) $skill_base_id, (int) $skill_tref_id);

            $this->keepAssignmentParameters();
            $form = $this->buildSkillQuestionAssignmentPropertiesForm($question_gui->getObject(), $assignment);
            if (!$form->checkInput()
                || !$this->checkPointsAreInt($form)) {
                $form->setValuesByPost();
                $this->showSkillQuestionAssignmentPropertiesFormCmd($question_gui, $assignment, $form);
                return;
            }
            $form->setValuesByPost();

            if ($form->getItemByPostVar('eval_mode')) {
                $assignment->setEvalMode($form->getItemByPostVar('eval_mode')->getValue());
            } else {
                $assignment->setEvalMode(ilAssQuestionSkillAssignment::EVAL_MODE_BY_QUESTION_RESULT);
            }

            if ($assignment->hasEvalModeBySolution()) {
                /** @var ?ilLogicalAnswerComparisonExpressionInputGUI $sol_cmp_expr_input */
                $sol_cmp_expr_input = $form->getItemByPostVar('solution_compare_expressions');

                if (
                    $sol_cmp_expr_input instanceof ilLogicalAnswerComparisonExpressionInputGUI
                    && !$this->checkSolutionCompareExpressionInput($sol_cmp_expr_input, $question_gui->getObject())
                ) {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
                    $this->showSkillQuestionAssignmentPropertiesFormCmd($question_gui, $assignment, $form);
                    return;
                }

                $assignment->initSolutionComparisonExpressionList();
                $assignment->getSolutionComparisonExpressionList()->reset();

                foreach ($sol_cmp_expr_input?->getValues() ?? [] as $expression) {
                    $assignment->getSolutionComparisonExpressionList()->add($expression);
                }
            } else {
                $assignment->setSkillPoints($form->getItemByPostVar('q_res_skill_points')->getValue());
            }

            $assignment->saveToDb();

            // add skill usage
            $this->skill_usage_service->addUsage(
                $this->getQuestionContainerId(),
                $this->request_data_collector->int('skill_base_id'),
                $this->request_data_collector->int('skill_tref_id')
            );

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('qpl_qst_skl_assign_properties_modified'), true);

            if ($this->isSyncOriginalPossibleAndAllowed($question_id)) {
                $this->ctrl->redirect($this, self::CMD_SHOW_SYNC_ORIGINAL_CONFIRMATION);
            }
        }

        $this->ctrl->redirect($this, self::CMD_EDIT_SKILL_QUEST_ASSIGNS);
    }

    private function buildSkillQuestionAssignmentPropertiesForm(
        assQuestion $question,
        ilAssQuestionSkillAssignment $assignment
    ): ilAssQuestionSkillAssignmentPropertyFormGUI {
        $form = new ilAssQuestionSkillAssignmentPropertyFormGUI($this);
        $form->setQuestion($question);
        $form->setAssignment($assignment);
        $form->setManipulationEnabled($this->isAssignmentEditingEnabled());
        $form->build();

        return $form;
    }

    private function showSkillQuestionAssignmentsCmd(bool $load_skill_points_from_request = false): void
    {
        $this->handleAssignmentConfigurationHintMessage();

        $assignment_list = $this->buildSkillQuestionAssignmentList();
        $assignment_list->loadFromDb();
        $assignment_list->loadAdditionalSkillData();

        /** @var string $mode */
        $mode = $this->http->wrapper()->query()->retrieve(
            SkillsByQuestionOverviewTable::VIEW_CONTROL_QUERY_PARAM,
            $this->refinery->byTrying([
              $this->refinery->kindlyTo()->string(),
              $this->refinery->always(SkillAssignmentViewControlMode::ALL->value),
            ])
        );

        $edit_uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                self::class,
                self::CMD_EDIT_SKILL_QUEST_ASSIGNS
            )
        );

        $this->tpl->setContent($this->ui_renderer->render(
            (new SkillsByQuestionOverviewTable(
                $this->question_list,
                $assignment_list,
                $this->ui_factory,
                $this->lng,
                $this->ctrl,
            ))->getComponents(
                $edit_uri,
                SkillAssignmentViewControlMode::tryFrom($mode) ?? SkillAssignmentViewControlMode::ALL
            )
        ));

        if (
            $this->test_object instanceof ilObjTest
            && $this->test_object->isSkillServiceToBeConsidered()
            && $this->hasFixedQuestionSetSkillAssignsLowerThanBarrier()
        ) {
            $this->tpl->setOnScreenMessage('info', $this->getSkillAssignBarrierInfo());
        }
    }

    private function hasFixedQuestionSetSkillAssignsLowerThanBarrier(): bool
    {
        if (!$this->test_object?->isFixedTest()) {
            return false;
        }

        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($this->test_object->getId());
        $assignmentList->loadFromDb();

        return $assignmentList->hasSkillsAssignedLowerThanBarrier();
    }

    private function getSkillAssignBarrierInfo(): string
    {
        return !$this->test_object instanceof ilObjTest
            ? ''
            : sprintf(
                $this->lng->txt('tst_skill_triggerings_num_req_answers_not_reached_warn'),
                $this->test_object->getGlobalSettings()->getSkillTriggeringNumberOfAnswers()
            );

    }

    private function editSkillQuestionAssignmentCmd(): void
    {
        $assignment_list = $this->buildSkillQuestionAssignmentList();
        $assignment_list->loadFromDb();
        $assignment_list->loadAdditionalSkillData();

        $this->keepAssignmentParameters();

        $edit_uri = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                self::class,
                self::CMD_SHOW_SKILL_QUEST_ASSIGN_PROPERTIES_FORM
            )
        );

        $this->ctrl->setParameterByClass(
            ilAssQuestionSkillAssignmentsGUI::class,
            'q_id',
            $this->request_data_collector->getQuestionId()
        );
        $this->toolbar->addComponent(
            $this->ui_factory->button()->standard(
                $this->lng->txt('tst_manage_competence_select_skills'),
                $this->ctrl->getLinkTargetByClass(
                    [ilAssQuestionSkillAssignmentsGUI::class],
                    self::CMD_SHOW_SKILL_SELECT
                )
            )->withUnavailableAction(!$this->isAssignmentEditingEnabled())
        );
        $this->ctrl->setParameterByClass(ilAssQuestionSkillAssignmentsGUI::class, 'q_id', null);

        $components = (new EditSkillsOfQuestionTable(
            $this->request_data_collector,
            $assignment_list,
            $this->ui_factory,
            $this->lng,
            (new EditSkillsOfQuestionTableActions(
                $this->tpl,
                [
                    EditSkillsOfQuestionTableEditAction::ACTION_ID => new EditSkillsOfQuestionTableEditAction(
                        $this->ui_factory,
                        $this->lng,
                        $this->isAssignmentEditingEnabled()
                    )
                ]
            ))
        ))->getComponents(new URLBuilder($edit_uri));

        $this->tpl->setContent($this->ui_renderer->render($components));
    }

    private function isSyncOriginalPossibleAndAllowed(int $questionId): bool
    {
        $questionData = $this->question_list->getDataArrayForQuestionId($questionId);

        if (!$questionData['original_id']) {
            return false;
        }

        $parentObjId = assQuestion::lookupParentObjId($questionData['original_id']);

        if ($parentObjId === null || !$this->doesObjectTypeMatch($parentObjId)) {
            return false;
        }

        foreach (ilObject::_getAllReferences($parentObjId) as $parentRefId) {
            if ($this->access->checkAccess('write', '', $parentRefId)) {
                return true;
            }
        }

        return false;
    }

    private function showSyncOriginalConfirmationCmd(): void
    {
        $confirmation = new ilConfirmationGUI();
        $confirmation->setHeaderText($this->lng->txt('qpl_sync_quest_skl_assigns_confirmation'));

        $confirmation->setFormAction($this->ctrl->getFormAction($this));
        $confirmation->addHiddenItem('q_id', $this->request_data_collector->getQuestionId());
        $confirmation->setConfirm($this->lng->txt('yes'), self::CMD_SYNC_ORIGINAL);
        $confirmation->setCancel($this->lng->txt('no'), self::CMD_EDIT_SKILL_QUEST_ASSIGNS);

        $this->tpl->setContent($this->ctrl->getHTML($confirmation));
    }

    private function syncOriginalCmd(): void
    {
        $question_id = $this->request_data_collector->getQuestionId();
        if ($this->isTestQuestion($question_id) && $this->isSyncOriginalPossibleAndAllowed($question_id)) {
            $question = assQuestion::instantiateQuestion($question_id);

            $question->syncSkillAssignments(
                $question->getObjId(),
                $question->getId(),
                $question->lookupParentObjId($question->getOriginalId()),
                $question->getOriginalId()
            );

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('qpl_qst_skl_assign_synced_to_orig'), true);
        }

        $this->ctrl->redirect($this, self::CMD_EDIT_SKILL_QUEST_ASSIGNS);
    }

    private function buildSkillQuestionAssignmentList(): ilAssQuestionSkillAssignmentList
    {
        $assignmentList = new ilAssQuestionSkillAssignmentList($this->db);
        $assignmentList->setParentObjId($this->getQuestionContainerId());

        return $assignmentList;
    }

    /**
     * @return ilSkillSelectorGUI
     */
    private function buildSkillSelectorExplorerGUI($assignments): ilSkillSelectorGUI
    {
        $skillSelectorExplorerGUI = new ilSkillSelectorGUI(
            $this,
            self::CMD_SHOW_SKILL_SELECT,
            $this,
            self::CMD_UPDATE_SKILL_QUEST_ASSIGNS,
            self::PARAM_SKILL_SELECTION
        );

        $skillSelectorExplorerGUI->setSelectMode(self::PARAM_SKILL_SELECTION, true);
        //$skillSelectorExplorerGUI->setNodeOnclickEnabled(false);

        // parameter name for skill selection is actually taken from value passed to constructor,
        // but passing a non empty name to setSelectMode is neccessary to keep input fields enabled

        foreach ($assignments as $assignment) {
            $id = "{$assignment->getSkillBaseId()}:{$assignment->getSkillTrefId()}";
            //$skillSelectorExplorerGUI->setNodeSelected($id);
            $skillSelectorExplorerGUI->setSkillSelected($id);
        }

        return $skillSelectorExplorerGUI;
    }

    /**
     * @return ilToolbarGUI
     */
    private function buildSkillSelectorToolbarGUI(): ilToolbarGUI
    {
        $skillSelectorToolbarGUI = new ilToolbarGUI();

        $skillSelectorToolbarGUI->setFormAction($this->ctrl->getFormAction($this));
        $skillSelectorToolbarGUI->addFormButton($this->lng->txt('qpl_save_skill_assigns_update'), self::CMD_UPDATE_SKILL_QUEST_ASSIGNS);
        $skillSelectorToolbarGUI->addFormButton($this->lng->txt('qpl_cancel_skill_assigns_update'), self::CMD_EDIT_SKILL_QUEST_ASSIGNS);

        return $skillSelectorToolbarGUI;
    }

    private function buildQuestionPage(assQuestionGUI $question_gui)
    {
        $this->tpl->addCss('./assets/css/content.css');

        $page_gui = new ilAssQuestionPageGUI($question_gui->getObject()->getId());
        $page_gui->setFileDownloadLink($question_gui->buildFileDownloadLink());
        $page_gui->setOutputMode("presentation");
        $page_gui->setRenderPageContainer(true);

        $page_gui->setPresentationTitle($question_gui->getObject()->getTitle());

        $question = $question_gui->getObject();
        $question->setShuffle(false); // dirty, but works ^^
        $question_gui->setObject($question);
        $page_gui->setQuestionHTML([
            $question_gui->getObject()->getId() => $question_gui->getSolutionOutput(0, 0, false, false, true, false, true, false, true)
        ]);

        return preg_replace("/src=\"\\.\\//ims", "src=\"" . ILIAS_HTTP_PATH . "/", $page_gui->presentation());

    }

    /**
     * @return ilAssQuestionSkillAssignment
     */
    private function buildQuestionSkillAssignment(
        int $question_id,
        int $skill_base_id,
        int $skill_tref_id
    ): ilAssQuestionSkillAssignment {
        $assignment = new ilAssQuestionSkillAssignment($this->db);

        $assignment->setParentObjId($this->getQuestionContainerId());
        $assignment->setQuestionId($question_id);
        $assignment->setSkillBaseId($skill_base_id);
        $assignment->setSkillTrefId($skill_tref_id);

        $assignment->loadFromDb();
        $assignment->loadAdditionalSkillData();

        return $assignment;
    }

    private function isTestQuestion($questionId): bool
    {
        return $this->question_list->isInList($questionId);
    }

    private function checkSolutionCompareExpressionInput(
        ilLogicalAnswerComparisonExpressionInputGUI $input,
        assQuestion $question
    ): bool {
        $errors = [];

        foreach ($input->getValues() as $expression) {
            $result = $this->validateSolutionCompareExpression($expression, $question);

            if ($result !== true) {
                $errors[] = "{$this->lng->txt('ass_lac_expression')} {$expression->getOrderIndex()}: {$result}";
            }
        }

        if ($errors !== []) {
            $input->setAlert($this->lng->txt('ass_lac_validation_error') . '<br />' . implode('<br />', $errors));
            return false;
        }

        return true;
    }

    private function checkPointsAreInt(ilPropertyFormGUI $form): bool
    {
        $points_result = $form->getInput('q_res_skill_points');
        $invalid_values_solution = array_filter(
            $form->getInput('solution_compare_expressions')['points'],
            fn(string $v): bool => $v != (int) $v
        );
        if ($points_result == (int) $points_result
            && $invalid_values_solution === []) {
            return true;
        }
        $this->tpl->setOnScreenMessage('failure', $this->lng->txt('numeric_only'));
        return false;
    }

    /**
     * @throws ilAssLacException
     */
    private function validateSolutionCompareExpression(
        ilAssQuestionSolutionComparisonExpression $expression,
        assQuestion $question
    ): bool|string {
        try {
            $question_provider = new ilAssLacQuestionProvider();
            $question_provider->setQuestion($question);
            (new ilAssLacCompositeValidator($question_provider))->validate(
                (new ilAssLacConditionParser())->parse($expression->getExpression())
            );
        } catch (ilAssLacException $e) {
            return $e instanceof ilAssLacFormAlertProvider ? $e->getFormAlert($this->lng) : throw $e;
        }

        return true;
    }

    private function keepAssignmentParameters(): void
    {
        $this->ctrl->saveParameter($this, 'q_id');
        $this->ctrl->saveParameter($this, 'skill_base_id');
        $this->ctrl->saveParameter($this, 'skill_tref_id');
    }

    private function handleAssignmentConfigurationHintMessage(): void
    {
        if ($this->getAssignmentConfigurationHintMessage()) {
            $this->tpl->setOnScreenMessage('info', $this->getAssignmentConfigurationHintMessage());
        }
    }

    private function getSkillSelectorHeader(int $questionId): string
    {
        return sprintf(
            $this->lng->txt('qpl_qst_skl_selection_for_question_header'),
            $this->question_list->getDataArrayForQuestionId($questionId)['title']
        );
    }

    private function sortAlphabetically($array)
    {
        $flags = SORT_REGULAR;

        if (defined('SORT_NATURAL')) {
            $flags = SORT_NATURAL;
        } elseif (defined('SORT_STRING')) {
            $flags = SORT_STRING;
        }

        if (defined('SORT_FLAG_CASE')) {
            $flags = $flags | SORT_FLAG_CASE;
        }

        asort($array, $flags);

        return $array;
    }

    protected function doesObjectTypeMatch($objectId): bool
    {
        return ilObject::_lookupType($objectId) == 'qpl';
    }
}
