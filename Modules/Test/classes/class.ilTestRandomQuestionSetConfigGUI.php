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
    private \ILIAS\Test\InternalRequestService $testrequest;

    public ilCtrlInterface $ctrl;
    public ?ilAccessHandler $access;
    public ilTabsGUI $tabs;
    public ilLanguage $lng;
    public ilGlobalTemplateInterface $tpl;
    public ilDBInterface $db;
    public ilTree $tree;
    public ilComponentRepository $component_repository;
    public ilObjectDefinition $objDefinition;
    public ilObjTest $testOBJ;
    protected ilTestRandomQuestionSetConfig $questionSetConfig;
    protected ilTestRandomQuestionSetSourcePoolDefinitionFactory $sourcePoolDefinitionFactory;
    protected ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList;
    protected ilTestRandomQuestionSetStagingPoolBuilder $stagingPool;
    protected ilTestRandomQuestionSetConfigStateMessageHandler $configStateMessageHandler;
    private ilTestProcessLockerFactory $processLockerFactory;

    /**
     * @var ArrayAccess
     */
    protected $dic;

    public function __construct(
        ilCtrl $ctrl,
        ilAccessHandler $access,
        ilTabsGUI $tabs,
        ilLanguage $lng,
        ilGlobalTemplateInterface $tpl,
        ilDBInterface $db,
        ilTree $tree,
        ilComponentRepository $component_repository,
        ilObjTest $testOBJ,
        ilTestProcessLockerFactory $processLockerFactory
    ) {
        global $DIC;
        $this->testrequest = $DIC->test()->internal()->request();

        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tabs = $tabs;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->db = $db;
        $this->tree = $tree;
        $this->component_repository = $component_repository;
        $this->testOBJ = $testOBJ;

        $this->dic = $DIC;
        $this->objDefinition = $this->dic['objDefinition'];

        $this->questionSetConfig = new ilTestRandomQuestionSetConfig(
            $this->tree,
            $this->db,
            $this->component_repository,
            $this->testOBJ
        );
        $this->questionSetConfig->loadFromDb();

        $this->sourcePoolDefinitionFactory = new ilTestRandomQuestionSetSourcePoolDefinitionFactory(
            $this->db,
            $this->testOBJ
        );

        $this->sourcePoolDefinitionList = new ilTestRandomQuestionSetSourcePoolDefinitionList(
            $this->db,
            $this->testOBJ,
            $this->sourcePoolDefinitionFactory
        );

        $this->sourcePoolDefinitionList->loadDefinitions();

        $this->stagingPool = new ilTestRandomQuestionSetStagingPoolBuilder(
            $this->db,
            $this->testOBJ
        );

        $this->configStateMessageHandler = new ilTestRandomQuestionSetConfigStateMessageHandler(
            $this->lng,
            $this->ctrl
        );

        $this->configStateMessageHandler->setTargetGUI($this);
        $this->configStateMessageHandler->setQuestionSetConfig($this->questionSetConfig);
        $this->configStateMessageHandler->setParticipantDataExists($this->testOBJ->participantDataExist());
        $this->configStateMessageHandler->setLostPools($this->sourcePoolDefinitionList->getLostPools());
        $this->processLockerFactory = $processLockerFactory;
    }

    public function executeCommand(): void
    {
        if (!$this->access->checkAccess("write", "", $this->testOBJ->getRefId())) {
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
                $this->questionSetConfig->loadFromDb();
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
        if ($this->testOBJ->participantDataExist()) {
            return true;
        }

        if ($this->sourcePoolDefinitionList->hasLostPool()) {
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
        if ($this->sourcePoolDefinitionList->areAllUsedPoolsAvailable()) {
            $locker = $this->processLockerFactory->retrieveLockerForNamedOperation();
            $locker->executeNamedOperation(__FUNCTION__, function (): void {
                $this->stagingPool->rebuild($this->sourcePoolDefinitionList);
                $this->sourcePoolDefinitionList->saveDefinitions();

                $this->questionSetConfig->loadFromDb();
                $this->questionSetConfig->setLastQuestionSyncTimestamp(time());
                $this->questionSetConfig->saveToDb();

                $this->testOBJ->saveCompleteStatus($this->questionSetConfig);

                $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_msg_random_question_set_synced"), true);
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
            $this->questionSetConfig->loadFromDb();
            $form = $this->buildGeneralConfigFormGUI($disabled_form);
        }

        $this->tpl->setContent($this->ctrl->getHTML($form));

        if (!$disabled_form) {
            $this->configStateMessageHandler->setContext(
                ilTestRandomQuestionSetConfigStateMessageHandler::CONTEXT_GENERAL_CONFIG
            );

            $this->configStateMessageHandler->handle();

            if ($this->configStateMessageHandler->hasValidationReports()) {
                if ($this->configStateMessageHandler->isValidationFailed()) {
                    $this->tpl->setOnScreenMessage('failure', $this->configStateMessageHandler->getValidationReportHtml());
                } else {
                    $this->tpl->setOnScreenMessage('info', $this->configStateMessageHandler->getValidationReportHtml());
                }
            }

            if ($this->testrequest->isset('modified') && (int) $this->testrequest->raw('modified')) {
                $this->tpl->setOnScreenMessage('success', $this->getGeneralModificationSuccessMessage());
            }
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

        $this->questionSetConfig->setLastQuestionSyncTimestamp(0);
        $this->questionSetConfig->saveToDb();

        $this->testOBJ->saveCompleteStatus($this->questionSetConfig);

        $this->ctrl->setParameter($this, 'modified', 1);
        $this->ctrl->redirect($this, self::CMD_SHOW_GENERAL_CONFIG_FORM);
    }


    private function buildGeneralConfigFormGUI(bool $disabled = false): ilTestRandomQuestionSetGeneralConfigFormGUI
    {
        $form = new ilTestRandomQuestionSetGeneralConfigFormGUI(
            $this->ctrl,
            $this->lng,
            $this->testOBJ,
            $this,
            $this->questionSetConfig
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
        $this->questionSetConfig->loadFromDb();

        $disabled_form = $this->preventFormBecauseOfSync();

        $content = '';

        if (!$this->isFrozenConfigRequired() && !$disabled_form) {
            $toolbar = $this->buildSourcePoolDefinitionListToolbarGUI();
            $content .= $this->ctrl->getHTML($toolbar);
        }

        $table = $this->buildSourcePoolDefinitionListTableGUI($disabled_form);
        $table->init($this->sourcePoolDefinitionList);
        $content .= $this->ctrl->getHTML($table);

        if ($this->sourcePoolDefinitionList->areAllUsedPoolsAvailable()) {
            $table = $this->buildNonAvailablePoolsTableGUI();
            $table->init($this->sourcePoolDefinitionList);
            $content .= $this->ctrl->getHTML($table);
        }

        $this->tpl->setContent($content);

        if ($disabled_form) {
            return;
        }

        $this->configStateMessageHandler->setContext(
            ilTestRandomQuestionSetConfigStateMessageHandler::CONTEXT_POOL_SELECTION
        );

        $this->configStateMessageHandler->handle();

        if ($this->configStateMessageHandler->hasValidationReports()) {
            if ($this->configStateMessageHandler->isValidationFailed()) {
                $this->tpl->setOnScreenMessage('failure', $this->configStateMessageHandler->getValidationReportHtml());
            } else {
                $this->tpl->setOnScreenMessage('info', $this->configStateMessageHandler->getValidationReportHtml());
            }
        }

        if ($this->testrequest->isset('modified') && (int) $this->testrequest->raw('modified')) {
            $this->tpl->setOnScreenMessage('success', $this->getGeneralModificationSuccessMessage());
        }
    }

    private function saveSourcePoolDefinitionListCmd(): void
    {
        $this->questionSetConfig->loadFromDb();

        $table = $this->buildSourcePoolDefinitionListTableGUI();

        $table->applySubmit($this->sourcePoolDefinitionList);

        $this->sourcePoolDefinitionList->reindexPositions();
        $this->sourcePoolDefinitionList->saveDefinitions();

        // fau: delayCopyRandomQuestions - don't rebuild the staging pool, just clear the sycn timestamp
        #$this->stagingPool->rebuild( $this->sourcePoolDefinitionList );
        #$this->sourcePoolDefinitionList->saveDefinitions();
        #$this->questionSetConfig->setLastQuestionSyncTimestamp(time());
        $this->questionSetConfig->setLastQuestionSyncTimestamp(0);
        // fau.
        $this->questionSetConfig->saveToDb();

        $this->testOBJ->saveCompleteStatus($this->questionSetConfig);

        $this->ctrl->setParameter($this, 'modified', 1);
        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    private function buildSourcePoolDefinitionListToolbarGUI(): ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI
    {
        $toolbar = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $this->ctrl,
            $this->lng,
            $this,
            $this->questionSetConfig
        );

        $toolbar->build();

        return $toolbar;
    }

    private function buildSourcePoolDefinitionListTableGUI(bool $disabled = false): ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI
    {
        $table = new ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI(
            $this->ctrl,
            $this->lng,
            $this,
            self::CMD_SHOW_SRC_POOL_DEF_LIST
        );

        if (!$this->isFrozenConfigRequired()) {
            $table->setDefinitionEditModeEnabled(true);
        }

        $table->setQuestionAmountColumnEnabled(
            $this->questionSetConfig->isQuestionAmountConfigurationModePerPool()
        );

        // fau: taxFilter/typeFilter - show the mapped taxonomy filters if pools are synced
        $table->setShowMappedTaxonomyFilter(
            $this->questionSetConfig->getLastQuestionSyncTimestamp() != 0
        );
        // fau.

        $translater = new ilTestTaxonomyFilterLabelTranslater($this->db);
        $translater->loadLabels($this->sourcePoolDefinitionList);
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

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_msg_source_pool_definitions_deleted"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    private function deleteMultipleSourcePoolDefinitionsCmd(): void
    {
        $definitionIds = $this->fetchMultiSourcePoolDefinitionIdsParameter();
        $this->deleteSourcePoolDefinitions($definitionIds);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_msg_source_pool_definitions_deleted"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    private function deleteSourcePoolDefinitions($definitionIds): void
    {
        foreach ($definitionIds as $definitionId) {
            $definition = $this->sourcePoolDefinitionFactory->getSourcePoolDefinitionByDefinitionId($definitionId);
            $definition->deleteFromDb();
        }

        $this->sourcePoolDefinitionList->reindexPositions();
        $this->sourcePoolDefinitionList->saveDefinitions();

        $this->questionSetConfig->loadFromDb();
        $this->questionSetConfig->setLastQuestionSyncTimestamp(0);
        $this->questionSetConfig->saveToDb();

        $this->testOBJ->saveCompleteStatus($this->questionSetConfig);
    }

    // hey: randomPoolSelector - new pool selector explorer command
    protected function showPoolSelectorExplorerCmd(): void
    {
        $this->questionSetConfig->loadFromDb();

        $selector = new ilTestQuestionPoolSelectorExplorer(
            $this,
            self::CMD_SHOW_POOL_SELECTOR_EXPLORER,
            self::CMD_SHOW_CREATE_SRC_POOL_DEF_FORM
        );

        $selector->setAvailableQuestionPools(
            array_keys($this->questionSetConfig->getSelectableQuestionPools())
        );

        if ($selector->handleCommand()) {
            return;
        }

        $this->tpl->setContent($selector->getHTML());
    }
    // hey.

    private function showCreateSourcePoolDefinitionFormCmd(ilTestRandomQuestionSetPoolDefinitionFormGUI $form = null): void
    {
        $this->questionSetConfig->loadFromDb();

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
        $this->questionSetConfig->loadFromDb();

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

        $sourcePoolDefinition->setSequencePosition($this->sourcePoolDefinitionList->getNextPosition());
        $sourcePoolDefinition->saveToDb();
        $this->sourcePoolDefinitionList->addDefinition($sourcePoolDefinition);

        $this->sourcePoolDefinitionList->saveDefinitions();

        $this->questionSetConfig->setLastQuestionSyncTimestamp(0);
        $this->questionSetConfig->saveToDb();

        $this->testOBJ->saveCompleteStatus($this->questionSetConfig);

        if ($redirect_back_to_form) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_msg_random_qsc_modified_add_new_rule"), true);
            $this->ctrl->setParameter($this, 'src_pool_def_id', $sourcePoolDefinition->getId());
            $this->ctrl->setParameter($this, 'quest_pool_id', $sourcePoolDefinition->getPoolId());
            $this->ctrl->redirect($this, self::CMD_SHOW_CREATE_SRC_POOL_DEF_FORM);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_msg_random_question_set_config_modified"), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
        }
    }

    private function buildCreateSourcePoolDefinitionFormGUI(): ilTestRandomQuestionSetPoolDefinitionFormGUI
    {
        $form = new ilTestRandomQuestionSetPoolDefinitionFormGUI(
            $this->ctrl,
            $this->lng,
            $this->testOBJ,
            $this,
            $this->questionSetConfig
        );

        $form->setSaveCommand(self::CMD_SAVE_CREATE_SRC_POOL_DEF_FORM);
        $form->setSaveAndNewCommand(self::CMD_SAVE_AND_NEW_CREATE_SRC_POOL_DEF_FORM);

        return $form;
    }

    private function showEditSourcePoolDefinitionFormCmd(ilTestRandomQuestionSetPoolDefinitionFormGUI $form = null): void
    {
        $this->questionSetConfig->loadFromDb();

        $defId = $this->fetchSingleSourcePoolDefinitionIdParameter();
        $sourcePoolDefinition = $this->sourcePoolDefinitionFactory->getSourcePoolDefinitionByDefinitionId($defId);
        $availableTaxonomyIds = ilObjTaxonomy::getUsageOfObject($sourcePoolDefinition->getPoolId());

        if ($form === null) {
            $form = $this->buildEditSourcePoolDefinitionFormGUI();
            $form->build($sourcePoolDefinition, $availableTaxonomyIds);
        }

        $this->tpl->setContent($this->ctrl->getHTML($form));
    }

    private function saveEditSourcePoolDefinitionFormCmd(): void
    {
        $this->questionSetConfig->loadFromDb();

        $defId = $this->fetchSingleSourcePoolDefinitionIdParameter();
        $sourcePoolDefinition = $this->sourcePoolDefinitionFactory->getSourcePoolDefinitionByDefinitionId($defId);
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

        $this->questionSetConfig->setLastQuestionSyncTimestamp(0);
        $this->questionSetConfig->saveToDb();

        $this->testOBJ->saveCompleteStatus($this->questionSetConfig);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("tst_msg_random_question_set_config_modified"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    private function buildEditSourcePoolDefinitionFormGUI(): ilTestRandomQuestionSetPoolDefinitionFormGUI
    {
        $form = new ilTestRandomQuestionSetPoolDefinitionFormGUI(
            $this->ctrl,
            $this->lng,
            $this->testOBJ,
            $this,
            $this->questionSetConfig
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
            $objCache = $this->dic['ilObjDataCache'];
            return $objCache->lookupObjId((int) $this->testrequest->raw('quest_pool_ref'));
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
            throw new ilTestMissingSourcePoolDefinitionParameterException();
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
        $availablePools = $this->testOBJ->getAvailableQuestionpools(
            true,
            $this->questionSetConfig->arePoolsWithHomogeneousScoredQuestionsRequired(),
            false,
            true,
            true
        );

        if (isset($availablePools[$poolId])) {
            $originalPoolData = $availablePools[$poolId];

            $originalPoolData['qpl_path'] = $this->questionSetConfig->getQuestionPoolPathString($poolId);
            $originalPoolData['qpl_ref_id'] = $this->questionSetConfig->getFirstQuestionPoolRefIdByObjId($poolId);

            return $this->sourcePoolDefinitionFactory->getSourcePoolDefinitionByOriginalPoolData($originalPoolData);
        }

        throw new ilTestQuestionPoolNotAvailableAsSourcePoolException();
    }

    /**
     * @return int[]
     */
    protected function fetchPoolIdsParameter(): array
    {
        $poolIds = [];
        if ($this->testrequest->isset('derive_pool_ids') && is_array($this->testrequest->raw('derive_pool_ids'))) {
            $poolIds = [];

            foreach ($this->testrequest->raw('derive_pool_ids') as $poolId) {
                $poolIds[] = (int) $poolId;
            }
        } elseif ($this->testrequest->isset('derive_pool_ids') && preg_match('/^\d+(\:\d+)*$/', $this->testrequest->raw('derive_pool_ids'))) {
            $poolIds = explode(':', $this->testrequest->raw('derive_pool_ids'));
        } elseif ($this->testrequest->isset('derive_pool_id') && (int) $this->testrequest->raw('derive_pool_id')) {
            $poolIds = [(int) $this->testrequest->raw('derive_pool_id')];
        }

        return $poolIds;
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
        $explorer->setClickableTypes($this->objDefinition->getExplorerContainerTypes());
        $explorer->setSelectableTypes([]);

        if (!$explorer->handleCommand()) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('tst_please_select_target_for_pool_derives'));
            $this->tpl->setContent($this->ctrl->getHTML($explorer));
        }
    }

    private function deriveNewPoolsCmd(): void
    {
        $poolIds = $this->fetchPoolIdsParameter();
        $targetRef = $this->fetchTargetRefParameter();

        if (count($poolIds)) {
            foreach ($poolIds as $poolId) {
                $lostPool = $this->sourcePoolDefinitionList->getLostPool($poolId);

                $deriver = new ilTestRandomQuestionSetPoolDeriver($this->db, $this->component_repository, $this->testOBJ);
                $deriver->setSourcePoolDefinitionList($this->sourcePoolDefinitionList);
                $deriver->setTargetContainerRef($targetRef);
                $deriver->setOwnerId($this->dic['ilUser']->getId());
                $newPool = $deriver->derive($lostPool);

                $srcPoolDefinition = $this->sourcePoolDefinitionList->getDefinitionBySourcePoolId($newPool->getId());
                $srcPoolDefinition->setPoolTitle($newPool->getTitle());
                $srcPoolDefinition->setPoolPath($this->questionSetConfig->getQuestionPoolPathString($newPool->getId()));
                $srcPoolDefinition->setPoolRefId($this->questionSetConfig->getFirstQuestionPoolRefIdByObjId($newPool->getId()));
                $srcPoolDefinition->saveToDb();

                ilTestRandomQuestionSetStagingPoolQuestionList::updateSourceQuestionPoolId(
                    $this->testOBJ->getTestId(),
                    $lostPool->getId(),
                    $newPool->getId()
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
        $last_sync = $this->questionSetConfig->getLastQuestionSyncTimestamp();
        if ($last_sync !== null && $last_sync !== 0) {
            $return = true;

            $sync_date = new ilDateTime($last_sync, IL_CAL_UNIX);
            $msg = sprintf(
                $this->lng->txt('tst_msg_rand_quest_set_stage_pool_last_sync'),
                ilDatePresentation::formatDate($sync_date)
            );

            $href = $this->ctrl->getLinkTarget($this, self::CMD_RESET_POOLSYNC);
            $label = $this->lng->txt('tst_btn_reset_pool_sync');

            $buttons = [
                $this->dic->ui()->factory()->button()->standard($label, $href)
            ];

            $msgbox = $this->dic->ui()->factory()->messageBox()
            ->info($msg)
            ->withButtons($buttons);
            $message = $this->dic->ui()->renderer()->render($msgbox);
            $this->dic->ui()->mainTemplate()->setCurrentBlock('mess');
            $this->dic->ui()->mainTemplate()->setVariable('MESSAGE', $message);
            $this->dic->ui()->mainTemplate()->parseCurrentBlock();
        }
        return $return;
    }

    public function resetPoolSyncCmd(): void
    {
        $this->questionSetConfig->setLastQuestionSyncTimestamp(0);
        $this->questionSetConfig->saveToDb();
        $this->ctrl->redirect($this, self::CMD_SHOW_GENERAL_CONFIG_FORM);
    }
}
