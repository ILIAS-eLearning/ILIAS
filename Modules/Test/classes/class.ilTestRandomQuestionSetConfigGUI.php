<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetConfig.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionList.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetSourcePoolDefinitionFactory.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetStagingPoolBuilder.php';
require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetConfigStateMessageHandler.php';

require_once 'Services/Taxonomy/classes/class.ilObjTaxonomy.php';

/**
 * GUI class that manages the question set configuration for continues tests
 *
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
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
    const CMD_SHOW_GENERAL_CONFIG_FORM = 'showGeneralConfigForm';
    const CMD_SAVE_GENERAL_CONFIG_FORM = 'saveGeneralConfigForm';
    const CMD_SHOW_SRC_POOL_DEF_LIST = 'showSourcePoolDefinitionList';
    const CMD_SAVE_SRC_POOL_DEF_LIST = 'saveSourcePoolDefinitionList';
    const CMD_DELETE_SINGLE_SRC_POOL_DEF = 'deleteSingleSourcePoolDefinition';
    const CMD_DELETE_MULTI_SRC_POOL_DEFS = 'deleteMultipleSourcePoolDefinitions';
    const CMD_SHOW_POOL_SELECTOR_EXPLORER = 'showPoolSelectorExplorer';
    const CMD_SHOW_CREATE_SRC_POOL_DEF_FORM = 'showCreateSourcePoolDefinitionForm';
    const CMD_SAVE_CREATE_SRC_POOL_DEF_FORM = 'saveCreateSourcePoolDefinitionForm';
    const CMD_SAVE_AND_NEW_CREATE_SRC_POOL_DEF_FORM = 'saveCreateAndNewSourcePoolDefinitionForm';
    const CMD_SHOW_EDIT_SRC_POOL_DEF_FORM = 'showEditSourcePoolDefinitionForm';
    const CMD_SAVE_EDIT_SRC_POOL_DEF_FORM = 'saveEditSourcePoolDefinitionForm';
    const CMD_BUILD_QUESTION_STAGE = 'buildQuestionStage';
    const CMD_SELECT_DERIVATION_TARGET = 'selectPoolDerivationTarget';
    const CMD_DERIVE_NEW_POOLS = 'deriveNewPools';
    const CMD_RESET_POOLSYNC = 'resetPoolSync';

    const HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD = 'afterRebuildQuestionStageCmd';
    /**
     * @var ilCtrl
     */
    public $ctrl = null;

    /**
     * @var ilAccess
     */
    public $access = null;

    /**
     * @var ilTabsGUI
     */
    public $tabs = null;

    /**
     * @var ilLanguage
     */
    public $lng = null;

    /**
     * @var ilGlobalTemplateInterface
     */
    public $tpl = null;

    /**
     * @var ilDBInterface
     */
    public $db = null;

    /**
     * @var ilTree
     */
    public $tree = null;

    /**
     * @var ilPluginAdmin
     */
    public $pluginAdmin = null;

    /**
     * @var ilObjectDefinition
     */
    public $objDefinition = null;

    /**
     * @var ilObjTest
     */
    public $testOBJ = null;

    /**
     * @var ilTestRandomQuestionSetConfig
     */
    protected $questionSetConfig = null;

    /**
     * @var ilTestRandomQuestionSetSourcePoolDefinitionFactory
     */
    protected $sourcePoolDefinitionFactory = null;

    /**
     * @var ilTestRandomQuestionSetSourcePoolDefinitionList
     */
    protected $sourcePoolDefinitionList = null;

    /**
     * @var ilTestRandomQuestionSetStagingPoolBuilder
     */
    protected $stagingPool = null;

    /**
     * @var ilTestRandomQuestionSetConfigStateMessageHandler
     */
    protected $configStateMessageHandler;
    /**
     * @var ilTestProcessLockerFactory
     */
    private $processLockerFactory;

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
        ilPluginAdmin $pluginAdmin,
        ilObjTest $testOBJ,
        ilTestProcessLockerFactory $processLockerFactory
    ) {
        $this->ctrl = $ctrl;
        $this->access = $access;
        $this->tabs = $tabs;
        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->db = $db;
        $this->tree = $tree;
        $this->pluginAdmin = $pluginAdmin;
        $this->testOBJ = $testOBJ;

        global $DIC;
        $this->dic = $DIC;
        $this->objDefinition = $this->dic['objDefinition'];

        $this->questionSetConfig = new ilTestRandomQuestionSetConfig(
            $this->tree,
            $this->db,
            $this->pluginAdmin,
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

    public function executeCommand()
    {
        if (!$this->access->checkAccess("write", "", $this->testOBJ->getRefId())) {
            ilUtil::sendFailure($this->lng->txt("cannot_edit_test"), true);
            $this->ctrl->redirectByClass('ilObjTestGUI', "infoScreen");
        }

        if ($this->isAvoidManipulationRedirectRequired()) {
            ilUtil::sendFailure($this->lng->txt("tst_msg_cannot_modify_random_question_set_conf_due_to_part"), true);
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

    private function isAvoidManipulationRedirectRequired()
    {
        if (!$this->isFrozenConfigRequired()) {
            return false;
        }

        if (!$this->isManipulationCommand()) {
            return false;
        }

        return true;
    }

    private function isFrozenConfigRequired()
    {
        if ($this->testOBJ->participantDataExist()) {
            return true;
        }

        if ($this->sourcePoolDefinitionList->hasLostPool()) {
            return true;
        }

        return false;
    }

    private function isManipulationCommand()
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

    private function handleTabs()
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

    private function buildQuestionStageCmd() : void
    {
        if ($this->sourcePoolDefinitionList->areAllUsedPoolsAvailable()) {
            $locker = $this->processLockerFactory->retrieveLockerForNamedOperation();
            $locker->executeNamedOperation(__FUNCTION__, function () : void {
                $this->stagingPool->rebuild($this->sourcePoolDefinitionList);
                $this->sourcePoolDefinitionList->saveDefinitions();

                $this->questionSetConfig->loadFromDb();
                $this->questionSetConfig->setLastQuestionSyncTimestamp(time());
                $this->questionSetConfig->saveToDb();

                $this->testOBJ->saveCompleteStatus($this->questionSetConfig);

                ilUtil::sendSuccess($this->lng->txt("tst_msg_random_question_set_synced"), true);
            });
        }

        $this->ctrl->redirect($this, $this->fetchAfterRebuildQuestionStageCmdParameter());
    }

    private function fetchAfterRebuildQuestionStageCmdParameter()
    {
        if (!isset($_GET[self::HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD])) {
            return self::CMD_SHOW_GENERAL_CONFIG_FORM;
        }

        if (!strlen($_GET[self::HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD])) {
            return self::CMD_SHOW_GENERAL_CONFIG_FORM;
        }

        return $_GET[self::HTTP_PARAM_AFTER_REBUILD_QUESTION_STAGE_CMD];
    }

    private function showGeneralConfigFormCmd(ilTestRandomQuestionSetGeneralConfigFormGUI $form = null)
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
                    ilUtil::sendFailure(
                        $this->configStateMessageHandler->getValidationReportHtml()
                    );
                } else {
                    ilUtil::sendInfo($this->configStateMessageHandler->getValidationReportHtml());
                }
            }

            if (isset($_GET['modified']) && (int) $_GET['modified']) {
                ilUtil::sendSuccess($this->getGeneralModificationSuccessMessage());
            }
        }
    }

    private function saveGeneralConfigFormCmd()
    {
        $form = $this->buildGeneralConfigFormGUI();

        $errors = !$form->checkInput(); // ALWAYS CALL BEFORE setValuesByPost()
        $form->setValuesByPost(); // NEVER CALL THIS BEFORE checkInput()

        if ($errors) {
            return $this->showGeneralConfigFormCmd($form);
        }

        $form->save();

        $this->questionSetConfig->setLastQuestionSyncTimestamp(0);
        $this->questionSetConfig->saveToDb();

        $this->testOBJ->saveCompleteStatus($this->questionSetConfig);

        $this->ctrl->setParameter($this, 'modified', 1);
        $this->ctrl->redirect($this, self::CMD_SHOW_GENERAL_CONFIG_FORM);
    }

    private function buildGeneralConfigFormGUI(bool $disabled = false) : ilTestRandomQuestionSetGeneralConfigFormGUI
    {
        require_once 'Modules/Test/classes/forms/class.ilTestRandomQuestionSetGeneralConfigFormGUI.php';

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

    private function showSourcePoolDefinitionListCmd()
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

        if (!$this->sourcePoolDefinitionList->areAllUsedPoolsAvailable()) {
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
                ilUtil::sendFailure(
                    $this->configStateMessageHandler->getValidationReportHtml()
                );
            } else {
                ilUtil::sendInfo($this->configStateMessageHandler->getValidationReportHtml());
            }
        }

        if (isset($_GET['modified']) && (int) $_GET['modified']) {
            ilUtil::sendSuccess($this->getGeneralModificationSuccessMessage());
        }
    }

    private function saveSourcePoolDefinitionListCmd()
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

    private function buildSourcePoolDefinitionListToolbarGUI()
    {
        require_once 'Modules/Test/classes/toolbars/class.ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI.php';

        $toolbar = new ilTestRandomQuestionSetSourcePoolDefinitionListToolbarGUI(
            $this->ctrl,
            $this->lng,
            $this,
            $this->questionSetConfig
        );

        $toolbar->build();

        return $toolbar;
    }

    private function buildSourcePoolDefinitionListTableGUI(bool $disabled = false) : ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI
    {
        require_once 'Modules/Test/classes/tables/class.ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI.php';

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

        require_once 'Modules/Test/classes/class.ilTestTaxonomyFilterLabelTranslater.php';
        $translater = new ilTestTaxonomyFilterLabelTranslater($this->db);
        $translater->loadLabels($this->sourcePoolDefinitionList);
        $table->setTaxonomyFilterLabelTranslater($translater);

        if ($disabled) {
            $table->setDefinitionEditModeEnabled(false);
        }
        $table->build();

        return $table;
    }

    private function buildNonAvailablePoolsTableGUI()
    {
        require_once 'Modules/Test/classes/tables/class.ilTestRandomQuestionSetNonAvailablePoolsTableGUI.php';

        $table = new ilTestRandomQuestionSetNonAvailablePoolsTableGUI(
            $this->ctrl,
            $this->lng,
            $this,
            self::CMD_SHOW_SRC_POOL_DEF_LIST
        );

        $table->build();

        return $table;
    }

    private function deleteSingleSourcePoolDefinitionCmd()
    {
        $definitionId = $this->fetchSingleSourcePoolDefinitionIdParameter();
        $this->deleteSourcePoolDefinitions(array($definitionId));

        ilUtil::sendSuccess($this->lng->txt("tst_msg_source_pool_definitions_deleted"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    private function deleteMultipleSourcePoolDefinitionsCmd()
    {
        $definitionIds = $this->fetchMultiSourcePoolDefinitionIdsParameter();
        $this->deleteSourcePoolDefinitions($definitionIds);

        ilUtil::sendSuccess($this->lng->txt("tst_msg_source_pool_definitions_deleted"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    private function deleteSourcePoolDefinitions($definitionIds)
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
    protected function showPoolSelectorExplorerCmd()
    {
        $this->questionSetConfig->loadFromDb();

        require_once 'Services/Repository/classes/class.ilTestQuestionPoolSelectorExplorer.php';
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

    private function showCreateSourcePoolDefinitionFormCmd(ilTestRandomQuestionSetPoolDefinitionFormGUI $form = null)
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

    private function saveCreateAndNewSourcePoolDefinitionFormCmd()
    {
        $this->saveCreateSourcePoolDefinitionFormCmd(true);
    }

    /**
     * @param bool $redirect_back_to_form
     */
    private function saveCreateSourcePoolDefinitionFormCmd($redirect_back_to_form = false)
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
            return $this->showCreateSourcePoolDefinitionFormCmd($form);
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
            ilUtil::sendSuccess($this->lng->txt("tst_msg_random_qsc_modified_add_new_rule"), true);
            $this->ctrl->setParameter($this, 'src_pool_def_id', $sourcePoolDefinition->getId());
            $this->ctrl->setParameter($this, 'quest_pool_id', $sourcePoolDefinition->getPoolId());
            $this->ctrl->redirect($this, self::CMD_SHOW_CREATE_SRC_POOL_DEF_FORM);
        } else {
            ilUtil::sendSuccess($this->lng->txt("tst_msg_random_question_set_config_modified"), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
        }
    }

    private function buildCreateSourcePoolDefinitionFormGUI()
    {
        require_once 'Modules/Test/classes/forms/class.ilTestRandomQuestionSetPoolDefinitionFormGUI.php';

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

    private function showEditSourcePoolDefinitionFormCmd(ilTestRandomQuestionSetPoolDefinitionFormGUI $form = null)
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

    private function saveEditSourcePoolDefinitionFormCmd()
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
            return $this->showSourcePoolDefinitionListCmd($form);
        }

        $form->applySubmit($sourcePoolDefinition, $availableTaxonomyIds);

        $sourcePoolDefinition->saveToDb();

        $this->questionSetConfig->setLastQuestionSyncTimestamp(0);
        $this->questionSetConfig->saveToDb();

        $this->testOBJ->saveCompleteStatus($this->questionSetConfig);

        ilUtil::sendSuccess($this->lng->txt("tst_msg_random_question_set_config_modified"), true);
        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    private function buildEditSourcePoolDefinitionFormGUI()
    {
        require_once 'Modules/Test/classes/forms/class.ilTestRandomQuestionSetPoolDefinitionFormGUI.php';

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

    private function fetchQuestionPoolIdParameter()
    {
        if (isset($_POST['quest_pool_id']) && (int) $_POST['quest_pool_id']) {
            return (int) $_POST['quest_pool_id'];
        }

        if (isset($_GET['quest_pool_id']) && (int) $_GET['quest_pool_id']) {
            return (int) $_GET['quest_pool_id'];
        }

        if (isset($_GET['quest_pool_ref']) && (int) $_GET['quest_pool_ref']) {
            $objCache = $this->dic['ilObjDataCache'];
            return $objCache->lookupObjId((int) $_GET['quest_pool_ref']);
        }

        require_once 'Modules/Test/exceptions/class.ilTestMissingQuestionPoolIdParameterException.php';
        throw new ilTestMissingQuestionPoolIdParameterException();
    }

    private function fetchSingleSourcePoolDefinitionIdParameter()
    {
        if (isset($_POST['src_pool_def_id']) && (int) $_POST['src_pool_def_id']) {
            return (int) $_POST['src_pool_def_id'];
        }

        if (isset($_GET['src_pool_def_id']) && (int) $_GET['src_pool_def_id']) {
            return (int) $_GET['src_pool_def_id'];
        }

        require_once 'Modules/Test/exceptions/class.ilTestMissingSourcePoolDefinitionParameterException.php';
        throw new ilTestMissingSourcePoolDefinitionParameterException();
    }

    private function fetchMultiSourcePoolDefinitionIdsParameter()
    {
        if (!isset($_POST['src_pool_def_ids']) || !is_array($_POST['src_pool_def_ids'])) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('tst_please_select_source_pool'), true);
            $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
            return [];
        }

        $definitionIds = array();

        foreach ($_POST['src_pool_def_ids'] as $definitionId) {
            $definitionId = (int) $definitionId;

            if (!$definitionId) {
                require_once 'Modules/Test/exceptions/class.ilTestMissingSourcePoolDefinitionParameterException.php';
                throw new ilTestMissingSourcePoolDefinitionParameterException();
            }

            $definitionIds[] = $definitionId;
        }

        return $definitionIds;
    }

    private function getSourcePoolDefinitionByAvailableQuestionPoolId($poolId)
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

        require_once 'Modules/Test/exceptions/class.ilTestQuestionPoolNotAvailableAsSourcePoolException.php';
        throw new ilTestQuestionPoolNotAvailableAsSourcePoolException();
    }

    protected function fetchPoolIdsParameter()
    {
        if (isset($_POST['derive_pool_ids']) && is_array($_POST['derive_pool_ids'])) {
            $poolIds = array();

            foreach ($_POST['derive_pool_ids'] as $poolId) {
                $poolIds[] = (int) $poolId;
            }
        } elseif (isset($_GET['derive_pool_ids']) && preg_match('/^\d+(\:\d+)*$/', $_GET['derive_pool_ids'])) {
            $poolIds = explode(':', $_GET['derive_pool_ids']);
        } elseif (isset($_GET['derive_pool_id']) && (int) $_GET['derive_pool_id']) {
            $poolIds = array( (int) $_GET['derive_pool_id'] );
        }

        return $poolIds;
    }

    protected function fetchTargetRefParameter()
    {
        if (isset($_GET['target_ref']) && (int) $_GET['target_ref']) {
            return (int) $_GET['target_ref'];
        }

        return null;
    }

    private function selectPoolDerivationTargetCmd()
    {
        $this->ctrl->setParameter($this, 'derive_pool_ids', implode(':', $this->fetchPoolIdsParameter()));

        require_once 'Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php';
        $explorer = new ilRepositorySelectorExplorerGUI(
            $this,
            self::CMD_SELECT_DERIVATION_TARGET,
            $this,
            self::CMD_DERIVE_NEW_POOLS,
            'target_ref'
        );
        $explorer->setClickableTypes($this->objDefinition->getExplorerContainerTypes());
        $explorer->setSelectableTypes(array());

        if (!$explorer->handleCommand()) {
            ilUtil::sendInfo($this->lng->txt('tst_please_select_target_for_pool_derives'));
            $this->tpl->setContent($this->ctrl->getHTML($explorer));
        }
    }

    private function deriveNewPoolsCmd()
    {
        $poolIds = $this->fetchPoolIdsParameter();
        $targetRef = $this->fetchTargetRefParameter();

        if (!$this->access->checkAccess('write', '', $targetRef)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_permission"), true);
            $this->ctrl->setParameterByClass(ilObjTestGUI::class, 'ref_id', $this->testOBJ->getRefId());
            $this->ctrl->redirectByClass(ilObjTestGUI::class);
        }

        if (count($poolIds)) {
            require_once 'Modules/Test/classes/class.ilTestRandomQuestionSetPoolDeriver.php';

            foreach ($poolIds as $poolId) {
                $lostPool = $this->sourcePoolDefinitionList->getLostPool($poolId);

                $deriver = new ilTestRandomQuestionSetPoolDeriver($this->db, $this->pluginAdmin, $this->testOBJ);
                $deriver->setSourcePoolDefinitionList($this->sourcePoolDefinitionList);
                $deriver->setTargetContainerRef($targetRef);
                $deriver->setOwnerId($this->dic['ilUser']->getId());
                $newPool = $deriver->derive($lostPool);

                $srcPoolDefinition = $this->sourcePoolDefinitionList->getDefinitionBySourcePoolId($newPool->getId());
                $srcPoolDefinition->setPoolTitle($newPool->getTitle());
                $srcPoolDefinition->setPoolPath($this->questionSetConfig->getQuestionPoolPathString($newPool->getId()));
                $srcPoolDefinition->setPoolRefId($this->questionSetConfig->getFirstQuestionPoolRefIdByObjId((int) $newPool->getId()));
                $srcPoolDefinition->saveToDb();

                ilTestRandomQuestionSetStagingPoolQuestionList::updateSourceQuestionPoolId(
                    $this->testOBJ->getTestId(),
                    $lostPool->getId(),
                    $newPool->getId()
                );
            }

            ilUtil::sendSuccess($this->lng->txt('tst_non_available_pool_newly_derived'), true);
        }

        $this->ctrl->redirect($this, self::CMD_SHOW_SRC_POOL_DEF_LIST);
    }

    /**
     * @return string
     */
    private function getGeneralModificationSuccessMessage()
    {
        return $this->lng->txt("tst_msg_random_question_set_config_modified");
    }

    /**
     * @return string
     */
    public function getGeneralConfigTabLabel()
    {
        return $this->lng->txt('tst_rnd_quest_cfg_tab_general');
    }

    /**
     * @return string
     */
    public function getPoolConfigTabLabel()
    {
        return $this->lng->txt('tst_rnd_quest_cfg_tab_pool');
    }


    protected function preventFormBecauseOfSync() : bool
    {
        $return = false;
        $last_sync = (int) $this->questionSetConfig->getLastQuestionSyncTimestamp();

        if ($last_sync !== null && $last_sync !== 0 &&
            !$this->isFrozenConfigRequired() && $this->questionSetConfig->isQuestionSetBuildable()) {
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

    public function resetPoolSyncCmd() : void
    {
        $this->questionSetConfig->setLastQuestionSyncTimestamp(0);
        $this->questionSetConfig->saveToDb();
        $this->ctrl->redirect($this, self::CMD_SHOW_GENERAL_CONFIG_FORM);
    }
}
