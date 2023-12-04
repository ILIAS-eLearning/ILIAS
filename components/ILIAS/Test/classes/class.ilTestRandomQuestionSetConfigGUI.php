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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Test\InternalRequestService;
use ILIAS\TestQuestionPool\QuestionInfoService;

/**
 * GUI class that manages the question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @package		Modules/Test
 *
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetGeneralConfigFormGUI
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetNonAvailablePoolsTableGUI
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilRepositorySelectorExplorerGUI
 * @ilCtrl_Calls ilTestRandomQuestionSetConfigGUI: ilTestRandomQuestionSetPoolDefinitionFormGUI
 */
class ilTestRandomQuestionSetConfigGUI
{
    public const CMD_SHOW_GENERAL_CONFIG_FORM = 'showGeneralConfigForm';
    public const CMD_SAVE_GENERAL_CONFIG_FORM = 'saveGeneralConfigForm';
    public const CMD_SHOW_SRC_POOL_DEF_LIST = 'showSourcePoolDefinitionList';
    public const CMD_SAVE_SRC_POOL_DEF_LIST = 'saveSourcePoolDefinitionList';
    public const CMD_DELETE_SINGLE_SRC_POOL_DEF = 'deleteSingleSourcePoolDefinition';
    public const CMD_DELETE_MULTI_SRC_POOL_DEFS = 'deleteMultipleSourcePoolDefinitions';
    public const CMD_SHOW_POOL_SELECTOR_EXPLORER = 'showPoolSelectorExplorer';
    public const CMD_SHOW_CREATE_SRC_POOL_DEF_FORM = 'showCreateSourcePoolDefinitionForm';
    public const CMD_SAVE_CREATE_SRC_POOL_DEF_FORM = 'saveCreateSourcePoolDefinitionForm';
    public const CMD_SAVE_AND_NEW_CREATE_SRC_POOL_DEF_FORM = 'saveCreateAndNewSourcePoolDefinitionForm';
    public const CMD_SHOW_EDIT_SRC_POOL_DEF_FORM = 'showEditSourcePoolDefinitionForm';
    public const CMD_SAVE_EDIT_SRC_POOL_DEF_FORM = 'saveEditSourcePoolDefinitionForm';
    public const CMD_BUILD_QUESTION_STAGE = 'buildQuestionStage';
    public const CMD_SELECT_DERIVATION_TARGET = 'selectPoolDerivationTarget';
    public const CMD_DERIVE_NEW_POOLS = 'deriveNewPools';
    public const CMD_RESET_POOLSYNC = 'resetPoolSync';

    public const HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD = 'afterRebuildQuestionStageCmd';

    protected ilTestRandomQuestionSetConfig $question_set_config;
    protected ilTestRandomQuestionSetSourcePoolDefinitionFactory $source_pool_definition_factory;
    protected ilTestRandomQuestionSetSourcePoolDefinitionList $source_pool_definition_list;
    protected ilTestRandomQuestionSetStagingPoolBuilder $stagingPool;
    protected ilTestRandomQuestionSetConfigStateMessageHandler $configStateMessageHandler;

    public function __construct(
        private ilObjTest $test_obj,
        private ilCtrl $ctrl,
        private ilObjUser $user,
        private ilAccessHandler $access,
        private UIFactory $ui_factory,
        private UIRenderer $ui_renderer,
        private ilTabsGUI $tabs,
        private ilLanguage $lng,
        private ilLogger $log,
        private ilGlobalTemplateInterface $tpl,
        private ilDBInterface $db,
        private ilTree $tree,
        private ilComponentRepository $component_repository,
        private ilObjectDefinition $obj_definition,
        private ilObjectDataCache $obj_cache,
        private ilTestProcessLockerFactory $processLockerFactory,
        private InternalRequestService $testrequest,
        private QuestionInfoService $questioninfo
    ) {
        $this->question_set_config = new ilTestRandomQuestionSetConfig(
            $this->tree,
            $this->db,
            $this->lng,
            $this->log,
            $this->component_repository,
            $this->test_obj,
            $this->questioninfo
        );
        $this->question_set_config->loadFromDb();

        $this->source_pool_definition_factory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $this->db,
            $this->test_obj
        );

        $this->source_pool_definition_list = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->db,
            $this->test_obj,
            $this->source_pool_definition_factory
        );

        $this->source_pool_definition_list->loadDefinitions();

        $this->stagingPool = new ilTestRandomQuestionSetStagingPoolBuilder(
            $this->db,
            $this->log,
            $this->test_obj
        );

        $this->configStateMessageHandler = new ilTestRandomQuestionSetConfigStateMessageHandler(
            $this->lng,
            $this->ui_factory,
            $this->ui_renderer,
            $this->ctrl
        );

        $this->configStateMessageHandler->setTargetGUI($this);
        $this->configStateMessageHandler->setQuestionSetConfig($this->question_set_config);
        $this->configStateMessageHandler->setParticipantDataExists($this->test_obj->participantDataExist());
        $this->configStateMessageHandler->setLostPools($this->source_pool_definition_list->getLostPools());
        $this->processLockerFactory = $processLockerFactory;
    }

    public function executeCommand(): void
    {
        if (!$this->access->checkAccess("write", "", $this->test_obj->getRefId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirectByClass('ilObjTestGUI', "infoScreen");
        }

        if ($this->isAvoidManipulationRedirectRequired()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("tst_msg_cannot_modify_random_question_set_conf_due_to_part"), true);
            $this->ctrl->redirect($this);
        }

        $this->handleTabs();

        $nextClass = $this->ctrl->getNextClass();

        switch ($nextClass) {
            case 'iltestrandomquestionsetpooldefinitionformgui':
                $this->question_set_config->loadFromDb();
                $poolId = $this->fetchQuestionPoolIdParameter();
                $sourcePoolDefinition = $this->getSourcePoolDefinitionByAvailableQuestionPoolId($poolId);
                $availableTaxonomyIds = ilObjTaxonomy::getUsageOfObject($sourcePoolDefinition->getPoolId());
                $form = $this->buildCreateSourcePoolDefinitionFormGUI();
                $form->build($sourcePoolDefinition, $availableTaxonomyIds);

                $this->ctrl->forwardCommand($form);
                break;

            default:
                $cmd = $this->ctrl->getCmd(self::CMD_SHOW_GENERAL_CONFIG_FORM) . 'Cmd';
                $this->$cmd();
        }
    }

    private function isAvoidManipulationRedirectRequired(): bool
    {
        if (!$this->isFrozenConfigRequired()) {
            return false;
        }

        if (!$this->isManipulationCommand()) {
            return false;
        }

        return true;
    }

    private function isFrozenConfigRequired(): bool
    {
        if ($this->test_obj->participantDataExist()) {
            return true;
        }

        if ($this->source_pool_definition_list->hasLostPool()) {
            return true;
        }

        return false;
    }

    private function isManipulationCommand(): bool
    {
        switch ($this->ctrl->getCmd(self::CMD_SHOW_GENERAL_CONFIG_FORM)) {
            case self::CMD_SAVE_GENERAL_CONFIG_FORM:
            case self::CMD_SAVE_SRC_POOL_DEF_LIST:
            case self::CMD_DELETE_SINGLE_SRC_POOL_DEF:
            case self::CMD_DELETE_MULTI_SRC_POOL_DEFS:
            case self::CMD_SAVE_CREATE_SRC_POOL_DEF_FORM:
            case self::CMD_SAVE_EDIT_SRC_POOL_DEF_FORM:
            case self::CMD_SAVE_AND_NEW_CREATE_SRC_POOL_DEF_FORM:
            case self::CMD_BUILD_QUESTION_STAGE:

                return true;
        }

        return false;
    }

    private function handleTabs(): void
    {
        $this->tabs->activateTab('assQuestions');

        $this->tabs->addSubTab(
            'tstRandQuestSetGeneralConfig',
            $this->getGeneralConfigTabLabel(),
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW_GENERAL_CONFIG_FORM)
        );

        $this->tabs->addSubTab(
            'tstRandQuestSetPoolConfig',
            $this->getPoolConfigTabLabel(),
            $this->ctrl->getLinkTarget($this, self::CMD_SHOW_SRC_POOL_DEF_LIST)
        );

        switch ($this->ctrl->getCmd(self::CMD_SHOW_GENERAL_CONFIG_FORM)) {
            case self::CMD_SHOW_GENERAL_CONFIG_FORM:
            case self::CMD_SAVE_GENERAL_CONFIG_FORM:

                $this->tabs->activateSubTab('tstRandQuestSetGeneralConfig');
                break;

            case self::CMD_SHOW_SRC_POOL_DEF_LIST:
            case self::CMD_SAVE_SRC_POOL_DEF_LIST:
            case self::CMD_DELETE_SINGLE_SRC_POOL_DEF:
            case self::CMD_DELETE_MULTI_SRC_POOL_DEFS:
            case self::CMD_SHOW_CREATE_SRC_POOL_DEF_FORM:
            case self::CMD_SAVE_CREATE_SRC_POOL_DEF_FORM:
            case self::CMD_SHOW_EDIT_SRC_POOL_DEF_FORM:
            case self::CMD_SAVE_EDIT_SRC_POOL_DEF_FORM:

                $this->tabs->activateSubTab('tstRandQuestSetPoolConfig');
                break;

            default: $this->tabs->activateSubTab('nonTab');
        }
    }

    private function buildQuestionStageCmd(): void
    {
        if ($this->source_pool_definition_list->areAllUsedPoolsAvailable()) {
            $locker = $this->processLockerFactory->retrieveLockerForNamedOperation();
            $locker->executeNamedOperation(__FUNCTION__, function (): void {
                $this->stagingPool->rebuild($this->source_pool_definition_list);
                $this->source_pool_definition_list->saveDefinitions();

                $this->question_set_config->loadFromDb();
                $this->question_set_config->setLastQuestionSyncTimestamp(time());
                $this->question_set_config->saveToDb();

                $this->test_obj->saveCompleteStatus($this->question_set_config);

                $this->ctrl->setParameterByClass(self::class, 'modified', 'sync');
            });
        }

        $this->ctrl->redirect($this, $this->fetchAfterRebuildQuestionStageCmdParameter());
    }

    private function fetchAfterRebuildQuestionStageCmdParameter()
    {
        if (!$this->testrequest->isset(self::HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD)) {
            return self::CMD_SHOW_GENERAL_CONFIG_FORM;
        }

        if (!strlen($this->testrequest->raw(self::HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD))) {
            return self::CMD_SHOW_GENERAL_CONFIG_FORM;
        }

        return $this->testrequest->raw(self::HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD);
    }

    private function showGeneralConfigFormCmd(ilTestRandomQuestionSetGeneralConfigFormGUI $form = null): void
    {
        $disabled_form = $this->preventFormBecauseOfSync();

        if ($form === null) {
            $this->question_set_config->loadFromDb();
            $form = $this->buildGeneralConfigFormGUI($disabled_form);
        }

        $this->tpl->setContent($this->ctrl->getHTML($form));

        $this->configStateMessageHandler->setContext(
            ilTestRandomQuestionSetConfigStateMessageHandler::CONTEXT_GENERAL_CONFIG
        );

        $this->configStateMessageHandler->handle();

        $message = $this->buildOnScreenMessage();
        if ($message !== '') {
            $this->populateOnScreenMessage($message);
        }
    }

    private function saveGeneralConfigFormCmd(): void
    {
        $form = $this->buildGeneralConfigFormGUI();

        $errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
        $form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

        if ($errors) {
            $this->showGeneralConfigFormCmd($form);
            return;
        }

        $form->save();

        $this->question_set_config->setLastQuestionSyncTimestamp(0);
        $this->question_set_config->saveToDb();

        $this->test_obj->saveCompleteStatus($this->question_set_config);

        $this->ctrl->setParameter($this, 'modified', 'save');
        $this->ctrl->redirect($this, self::CMD_SHOW_GENERAL_CONFIG_FORM);
    }


    private function buildGeneralConfigFormGUI(bool $disabled = false): ilTestRandomQuestionSetGeneralConfigFormGUI
    {
        $form = new ilTestRandomQuestionSetGeneralConfigFormGUI(
            $this->ctrl,
            $this->lng,
            $this->test_obj,
            $this,
            $this->question_set_config
        );

        //TODO: should frozen config not lead to 'completely disabled' as well?!
        $form->setEditModeEnabled(!$this->isFrozenConfigRequired());

        if ($disabled) {
            $form->setEditModeEnabled(false);
        }

        $form->build();

        if ($disabled) {
            $form->clearCommandButtons();
        }

        return $form;
    }

    private function showSourcePoolDefinitionListCmd(): void
    {
        $disabled_form = $this->preventFormBecauseOfSync();

        $this->question_set_config->loadFromDb();

        $content = '';

        if (!$this->isFrozenConfigRequired() && !$disabled_form) {
            $toolbar = $this->buildSourcePoolDefinitionListToolbarGUI();
            $content .= $this->ctrl->getHTML($toolbar);
        }

        $table = $this->buildSourcePoolDefinitionListTableGUI($disabled_form);
        $table->init($this->source_pool_definition_list);
        $content .= $this->ctrl->getHTML($table);

        if (!$this->source_pool_definition_list->areAllUsedPoolsAvailable()) {
            $table = $this->buildNonAvailablePoolsTableGUI();
            $table->init($this->source_pool_definition_list);
            $content .= $this->ctrl->getHTML($table);
        }

        $this->tpl->setContent($content);

        $this->configStateMessageHandler->setContext(
            ilTestRandomQuestionSetConfigStateMessageHandler::CONTEXT_POOL_SELECTION
        );

        $this->configStateMessageHandler->handle();

        $message = $this->buildOnScreenMessage();
        if ($message) {
            $this->populateOnScreenMessage($message);
        }
    }

    private function saveSourcePoolDefinitionListCmd(): void
    {
        $this->question_set_config->loadFromDb();

        $table = $this->buildSourcePoolDefinitionListTableGUI();

        $table->applySubmit($this->source_pool_definition_list);

        $this->source_pool_definition_list->reindexPositions();
        $this->source_pool_definition_list->saveDefinitions();

        $this->question_set_config->setLastQuestionSyncTimestamp(0);
        // fau.
        $this->question_set_config->saveToDb();

        $this->test_obj->saveCompleteStatus($this->question_set_config);

        $this->ctrl->setParameter($this, 'modified', 'save');
        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    private function buildSourcePoolDefinitionListToolbarGUI(): ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI
    {
        $toolbar = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $this->ctrl,
            $this->lng,
            $this,
            $this->question_set_config
        );

        $toolbar->build();

        return $toolbar;
    }

    private function buildSourcePoolDefinitionListTableGUI(bool $disabled = false): ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI
    {
        $table = new ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI(
            $this,
            self::CMD_SHOW_SRC_POOL_DEF_LIST,
            $this->access,
            $this->ui_factory,
            $this->ui_renderer,
            $this->testrequest->raw('def_order') ?? [],
            $this->testrequest->raw('quest_amount') ?? []
        );

        if (!$this->isFrozenConfigRequired()) {
            $table->setDefinitionEditModeEnabled(true);
        }

        $table->setQuestionAmountColumnEnabled(
            $this->question_set_config->isQuestionAmountConfigurationModePerPool()
        );

        $table->setShowMappedTaxonomyFilter(
            $this->question_set_config->getLastQuestionSyncTimestamp() != 0
        );

        $translater = new ilTestQuestionFilterLabelTranslater($this->db, $this->lng);
        $translater->loadLabels($this->source_pool_definition_list);
        $table->setTaxonomyFilterLabelTranslater($translater);

        if ($disabled) {
            $table->setDefinitionEditModeEnabled(false);
        }
        $table->build();

        return $table;
    }

    private function buildNonAvailablePoolsTableGUI(): ilTestRandomQuestionSetNonAvailablePoolsTableGUI
    {
        $table = new ilTestRandomQuestionSetNonAvailablePoolsTableGUI(
            $this->ctrl,
            $this->lng,
            $this,
            self::CMD_SHOW_SRC_POOL_DEF_LIST
        );

        $table->build();

        return $table;
    }

    private function deleteSingleSourcePoolDefinitionCmd(): void
    {
        $definitionId = $this->fetchSingleSourcePoolDefinitionIdParameter();
        $this->deleteSourcePoolDefinitions([$definitionId]);

        $this->ctrl->setParameterByClass(self::class, 'modified', 'remove');
        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    private function deleteMultipleSourcePoolDefinitionsCmd(): void
    {
        $definitionIds = $this->fetchMultiSourcePoolDefinitionIdsParameter();
        $this->deleteSourcePoolDefinitions($definitionIds);

        $this->ctrl->setParameterByClass(self::class, 'modified', 'remove');
        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    private function deleteSourcePoolDefinitions($definitionIds): void
    {
        foreach ($definitionIds as $definitionId) {
            $definition = $this->source_pool_definition_factory->getSourcePoolDefinitionByDefinitionId($definitionId);
            $definition->deleteFromDb();
        }

        $this->source_pool_definition_list->reindexPositions();
        $this->source_pool_definition_list->saveDefinitions();

        $this->question_set_config->loadFromDb();
        $this->question_set_config->setLastQuestionSyncTimestamp(0);
        $this->question_set_config->saveToDb();

        $this->test_obj->saveCompleteStatus($this->question_set_config);
    }

    // hey: randomPoolSelector - new pool selector explorer command
    protected function showPoolSelectorExplorerCmd(): void
    {
        $this->question_set_config->loadFromDb();

        $selector = new ilTestQuestionPoolSelectorExplorer(
            $this,
            self::CMD_SHOW_POOL_SELECTOR_EXPLORER,
            self::CMD_SHOW_CREATE_SRC_POOL_DEF_FORM,
            $this->obj_cache
        );

        $selector->setAvailableQuestionPools(
            array_keys($this->question_set_config->getSelectableQuestionPools())
        );

        if ($selector->handleCommand()) {
            return;
        }

        $this->tpl->setContent($selector->getHTML());
    }
    // hey.

    private function showCreateSourcePoolDefinitionFormCmd(ilTestRandomQuestionSetPoolDefinitionFormGUI $form = null): void
    {
        $this->question_set_config->loadFromDb();

        $poolId = $this->fetchQuestionPoolIdParameter();

        $sourcePoolDefinition = $this->getSourcePoolDefinitionByAvailableQuestionPoolId($poolId);
        $availableTaxonomyIds = ilObjTaxonomy::getUsageOfObject($sourcePoolDefinition->getPoolId());

        if ($form === null) {
            $form = $this->buildCreateSourcePoolDefinitionFormGUI();
            $form->build($sourcePoolDefinition, $availableTaxonomyIds);
        }

        $this->tpl->setContent($this->ctrl->getHTML($form));
    }

    private function saveCreateAndNewSourcePoolDefinitionFormCmd(): void
    {
        $this->saveCreateSourcePoolDefinitionFormCmd(true);
    }

    /**
     * @param bool $redirect_back_to_form
     */
    private function saveCreateSourcePoolDefinitionFormCmd($redirect_back_to_form = false): void
    {
        $this->question_set_config->loadFromDb();

        $poolId = $this->fetchQuestionPoolIdParameter();
        $sourcePoolDefinition = $this->getSourcePoolDefinitionByAvailableQuestionPoolId($poolId);
        $availableTaxonomyIds = ilObjTaxonomy::getUsageOfObject($sourcePoolDefinition->getPoolId());

        $form = $this->buildCreateSourcePoolDefinitionFormGUI();
        $form->build($sourcePoolDefinition, $availableTaxonomyIds);

        $errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
        $form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

        if ($errors) {
            $this->showCreateSourcePoolDefinitionFormCmd($form);
            return;
        }

        $form->applySubmit($sourcePoolDefinition, $availableTaxonomyIds);

        $sourcePoolDefinition->setSequencePosition($this->source_pool_definition_list->getNextPosition());
        $sourcePoolDefinition->saveToDb();
        $this->source_pool_definition_list->addDefinition($sourcePoolDefinition);

        $this->source_pool_definition_list->saveDefinitions();

        $this->question_set_config->setLastQuestionSyncTimestamp(0);
        $this->question_set_config->saveToDb();

        $this->test_obj->saveCompleteStatus($this->question_set_config);

        if ($redirect_back_to_form) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_msg_random_qsc_modified_add_new_rule"), true);
            $this->ctrl->setParameter($this, 'src_pool_def_id', $sourcePoolDefinition->getId());
            $this->ctrl->setParameter($this, 'quest_pool_id', $sourcePoolDefinition->getPoolId());
            $this->ctrl->redirect($this, self::CMD_SHOW_CREATE_SRC_POOL_DEF_FORM);
        } else {
            $this->ctrl->setParameterByClass(self::class, 'modified', 'save');
            $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
        }
    }

    private function buildCreateSourcePoolDefinitionFormGUI(): ilTestRandomQuestionSetPoolDefinitionFormGUI
    {
        $form = new ilTestRandomQuestionSetPoolDefinitionFormGUI(
            $this->ctrl,
            $this->lng,
            $this->test_obj,
            $this,
            $this->question_set_config
        );

        $form->setSaveCommand(self::CMD_SAVE_CREATE_SRC_POOL_DEF_FORM);
        $form->setSaveAndNewCommand(self::CMD_SAVE_AND_NEW_CREATE_SRC_POOL_DEF_FORM);

        return $form;
    }

    private function showEditSourcePoolDefinitionFormCmd(ilTestRandomQuestionSetPoolDefinitionFormGUI $form = null): void
    {
        $this->question_set_config->loadFromDb();

        $defId = $this->fetchSingleSourcePoolDefinitionIdParameter();
        $sourcePoolDefinition = $this->source_pool_definition_factory->getSourcePoolDefinitionByDefinitionId($defId);
        $availableTaxonomyIds = ilObjTaxonomy::getUsageOfObject($sourcePoolDefinition->getPoolId());

        if ($form === null) {
            $form = $this->buildEditSourcePoolDefinitionFormGUI();
            $form->build($sourcePoolDefinition, $availableTaxonomyIds);
        }

        $this->tpl->setContent($this->ctrl->getHTML($form));
    }

    private function saveEditSourcePoolDefinitionFormCmd(): void
    {
        $this->question_set_config->loadFromDb();

        $defId = $this->fetchSingleSourcePoolDefinitionIdParameter();
        $sourcePoolDefinition = $this->source_pool_definition_factory->getSourcePoolDefinitionByDefinitionId($defId);
        $availableTaxonomyIds = ilObjTaxonomy::getUsageOfObject($sourcePoolDefinition->getPoolId());

        $form = $this->buildEditSourcePoolDefinitionFormGUI();
        $form->build($sourcePoolDefinition, $availableTaxonomyIds);

        $errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
        $form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

        if ($errors) {
            $this->showSourcePoolDefinitionListCmd();
            return;
        }

        $form->applySubmit($sourcePoolDefinition, $availableTaxonomyIds);

        $sourcePoolDefinition->saveToDb();

        $this->question_set_config->setLastQuestionSyncTimestamp(0);
        $this->question_set_config->saveToDb();

        $this->test_obj->saveCompleteStatus($this->question_set_config);

        $this->ctrl->setParameterByClass(self::class, 'modified', 'save');
        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    private function buildEditSourcePoolDefinitionFormGUI(): ilTestRandomQuestionSetPoolDefinitionFormGUI
    {
        $form = new ilTestRandomQuestionSetPoolDefinitionFormGUI(
            $this->ctrl,
            $this->lng,
            $this->test_obj,
            $this,
            $this->question_set_config
        );

        $form->setSaveCommand(self::CMD_SAVE_EDIT_SRC_POOL_DEF_FORM);

        return $form;
    }

    private function fetchQuestionPoolIdParameter(): int
    {
        if ($this->testrequest->isset('quest_pool_id') && (int) $this->testrequest->raw('quest_pool_id')) {
            return (int) $this->testrequest->raw('quest_pool_id');
        }

        if ($this->testrequest->isset('quest_pool_ref') && (int) $this->testrequest->raw('quest_pool_ref')) {
            return $this->obj_cache->lookupObjId((int) $this->testrequest->raw('quest_pool_ref'));
        }

        throw new ilTestMissingQuestionPoolIdParameterException();
    }

    private function fetchSingleSourcePoolDefinitionIdParameter(): int
    {
        if ($this->testrequest->isset('src_pool_def_id') && (int) $this->testrequest->raw('src_pool_def_id')) {
            return (int) $this->testrequest->raw('src_pool_def_id');
        }

        throw new ilTestMissingSourcePoolDefinitionParameterException();
    }

    private function fetchMultiSourcePoolDefinitionIdsParameter(): array
    {
        if (!$this->testrequest->isset('src_pool_def_ids') || !is_array($this->testrequest->raw('src_pool_def_ids'))) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_please_select_source_pool'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
            return [];
        }

        $definitionIds = [];

        foreach ($this->testrequest->raw('src_pool_def_ids') as $definitionId) {
            $definitionId = (int) $definitionId;

            if (!$definitionId) {
                throw new ilTestMissingSourcePoolDefinitionParameterException();
            }

            $definitionIds[] = $definitionId;
        }

        return $definitionIds;
    }

    private function getSourcePoolDefinitionByAvailableQuestionPoolId($poolId): ilTestRandomQuestionSetSourcePoolDefinition
    {
        $availablePools = $this->test_obj->getAvailableQuestionpools(
            true,
            $this->question_set_config->arePoolsWithHomogeneousScoredQuestionsRequired(),
            false,
            true,
            true
        );

        if (isset($availablePools[$poolId])) {
            $originalPoolData = $availablePools[$poolId];

            $originalPoolData['qpl_path'] = $this->question_set_config->getQuestionPoolPathString($poolId);
            $originalPoolData['qpl_ref_id'] = $this->question_set_config->getFirstQuestionPoolRefIdByObjId($poolId);

            return $this->source_pool_definition_factory->getSourcePoolDefinitionByOriginalPoolData($originalPoolData);
        }

        throw new ilTestQuestionPoolNotAvailableAsSourcePoolException();
    }

    /**
     * @return int[]
     */
    protected function fetchPoolIdsParameter(): array
    {
        $pool_ids = [];
        if ($this->testrequest->isset('derive_pool_ids') && is_array($this->testrequest->raw('derive_pool_ids'))) {
            $pool_ids = [];

            foreach ($this->testrequest->raw('derive_pool_ids') as $pool_id) {
                $pool_ids[] = (int) $pool_id;
            }
        } elseif ($this->testrequest->isset('derive_pool_ids') && preg_match('/^\d+(\:\d+)*$/', $this->testrequest->raw('derive_pool_ids'))) {
            $pool_ids = array_map(
                fn(int $id) => (int) $id,
                explode(':', $this->testrequest->raw('derive_pool_ids'))
            );
        } elseif ($this->testrequest->isset('derive_pool_id') && (int) $this->testrequest->raw('derive_pool_id')) {
            $pool_ids = [(int) $this->testrequest->raw('derive_pool_id')];
        }

        return $pool_ids;
    }

    protected function fetchTargetRefParameter(): ?int
    {
        if ($this->testrequest->isset('target_ref') && (int) $this->testrequest->raw('target_ref')) {
            return (int) $this->testrequest->raw('target_ref');
        }

        return null;
    }

    private function selectPoolDerivationTargetCmd(): void
    {
        $this->ctrl->setParameter($this, 'derive_pool_ids', implode(':', $this->fetchPoolIdsParameter()));

        $explorer = new ilRepositorySelectorExplorerGUI(
            $this,
            self::CMD_SELECT_DERIVATION_TARGET,
            $this,
            self::CMD_DERIVE_NEW_POOLS,
            'target_ref'
        );
        $explorer->setClickableTypes($this->obj_definition->getExplorerContainerTypes());
        $explorer->setSelectableTypes([]);

        if (!$explorer->handleCommand()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_please_select_target_for_pool_derives'));
            $this->tpl->setContent($this->ctrl->getHTML($explorer));
        }
    }

    private function deriveNewPoolsCmd(): void
    {
        $pool_ids = $this->fetchPoolIdsParameter();
        $target_ref = $this->fetchTargetRefParameter();
        if (!$this->access->checkAccess('write', '', $target_ref)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_permission"), true);
            $this->ctrl->setParameterByClass(ilObjTestGUI::class, 'ref_id', $this->test_obj->getRefId());
            $this->ctrl->redirectByClass(ilObjTestGUI::class);
        }

        if ($pool_ids !== []) {
            foreach ($pool_ids as $pool_id) {
                $lost_pool = $this->source_pool_definition_list->getLostPool($pool_id);

                $deriver = new ilTestRandomQuestionSetPoolDeriver($this->db, $this->component_repository, $this->test_obj);
                $deriver->setSourcePoolDefinitionList($this->source_pool_definition_list);
                $deriver->setTargetContainerRef($target_ref);
                $deriver->setOwnerId($this->user->getId());
                $new_pool = $deriver->derive($lost_pool);

                $srcPoolDefinition = $this->source_pool_definition_list->getDefinitionBySourcePoolId($new_pool->getId());
                $srcPoolDefinition->setPoolTitle($new_pool->getTitle());
                $srcPoolDefinition->setPoolPath($this->question_set_config->getQuestionPoolPathString($new_pool->getId()));
                $srcPoolDefinition->setPoolRefId($this->question_set_config->getFirstQuestionPoolRefIdByObjId($new_pool->getId()));
                $srcPoolDefinition->saveToDb();

                ilTestRandomQuestionSetStagingPoolQuestionList::updateSourceQuestionPoolId(
                    $this->test_obj->getTestId(),
                    $lost_pool->getId(),
                    $new_pool->getId()
                );
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt('tst_non_available_pool_newly_derived'), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    /**
     * @return string
     */
    private function getGeneralModificationSuccessMessage(): string
    {
        return $this->lng->txt("tst_msg_random_question_set_config_modified");
    }

    /**
     * @return string
     */
    public function getGeneralConfigTabLabel(): string
    {
        return $this->lng->txt('tst_rnd_quest_cfg_tab_general');
    }

    /**
     * @return string
     */
    public function getPoolConfigTabLabel(): string
    {
        return $this->lng->txt('tst_rnd_quest_cfg_tab_pool');
    }


    protected function preventFormBecauseOfSync(): bool
    {
        $return = false;
        $last_sync = $this->question_set_config->getLastQuestionSyncTimestamp();

        if ($last_sync !== null && $last_sync !== 0 &&
            !$this->isFrozenConfigRequired() && $this->question_set_config->isQuestionSetBuildable()) {
            return true;
        }
        return false;
    }

    public function resetPoolSyncCmd(): void
    {
        $this->question_set_config->setLastQuestionSyncTimestamp(0);
        $this->question_set_config->saveToDb();
        $this->ctrl->redirect($this, self::CMD_SHOW_GENERAL_CONFIG_FORM);
    }

    protected function buildOnScreenMessage(): string
    {
        $message = $this->buildFormResultMessage();
        $message .= $this->buildValidationMessage();

        return $message;
    }

    protected function buildFormResultMessage(): string
    {
        $message = '';

        if ($this->testrequest->isset('modified')) {
            $action = $this->testrequest->raw('modified');
            if ($action === 'save') {
                $success_message = $this->ui_factory->messageBox()->success($this->getGeneralModificationSuccessMessage());
            } elseif ($action === 'remove') {
                $success_message = $this->ui_factory->messageBox()->success($this->lng->txt("tst_msg_source_pool_definitions_deleted"));
            } elseif ($action === 'sync') {
                $success_message = $this->ui_factory->messageBox()->success($this->lng->txt("tst_msg_random_question_set_synced"));
            }
            $message .= $this->ui_renderer->render(
                $success_message
            );
        }

        return $message;
    }

    protected function buildValidationMessage(): string
    {
        if ($this->configStateMessageHandler->isValidationFailed()) {
            return $this->ui_renderer->render(
                $this->ui_factory->messageBox()->failure($this->configStateMessageHandler->getValidationReportHtml())
            );
        }

        if ($this->configStateMessageHandler->hasValidationReports()) {
            return $this->ui_renderer->render(
                $this->ui_factory->messageBox()->info($this->configStateMessageHandler->getValidationReportHtml())
            );
        }

        return $this->configStateMessageHandler->getSyncInfoMessage();
    }

    /**
     * @param $message
     */
    protected function populateOnScreenMessage($message)
    {
        $this->tpl->setCurrentBlock('mess');
        $this->tpl->setVariable('MESSAGE', $message);
        $this->tpl->parseCurrentBlock();
    }
}
