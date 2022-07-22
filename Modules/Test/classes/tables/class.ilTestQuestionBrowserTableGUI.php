<?php declare(strict_types=1);

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
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @ilCtrl_Calls ilTestQuestionBrowserTableGUI: ilFormPropertyDispatchGUI
 */
class ilTestQuestionBrowserTableGUI extends ilTable2GUI
{
    private const REPOSITORY_ROOT_NODE_ID = 1;

    public const CONTEXT_PARAMETER = 'question_browse_context';
    public const CONTEXT_PAGE_VIEW = 'contextPageView';
    public const CONTEXT_LIST_VIEW = 'contextListView';

    public const MODE_PARAMETER = 'question_browse_mode';
    public const MODE_BROWSE_POOLS = 'modeBrowsePools';
    public const MODE_BROWSE_TESTS = 'modeBrowseTests';

    public const CMD_BROWSE_QUESTIONS = 'browseQuestions';
    public const CMD_APPLY_FILTER = 'applyFilter';
    public const CMD_RESET_FILTER = 'resetFilter';
    public const CMD_INSERT_QUESTIONS = 'insertQuestions';

    private \ILIAS\Test\InternalRequestService $testrequest;
    private \ILIAS\HTTP\GlobalHttpState $httpState;
    private \ILIAS\Refinery\Factory $refinery;
    private bool $writeAccess = false;
    private ilGlobalTemplateInterface $mainTpl;
    private ilTabsGUI $tabs;
    private ilTree $tree;
    private ilDBInterface $db;
    private ilComponentRepository $component_repository;
    private ilObjTest $testOBJ;
    private ilAccessHandler $access;

    /** @var array<string, mixed> */
    private array $filter = [];

    public function __construct(
        ilCtrl $ctrl,
        ilGlobalTemplateInterface $mainTpl,
        ilTabsGUI $tabs,
        ilLanguage $lng,
        ilTree $tree,
        ilDBInterface $db,
        ilComponentRepository $component_repository,
        ilObjTest $testOBJ,
        ilAccessHandler $access,
        ILIAS\HTTP\GlobalHttpState $httpState,
        ILIAS\Refinery\Factory $refinery
    ) {
        $this->ctrl = $ctrl;
        $this->mainTpl = $mainTpl;
        $this->tabs = $tabs;
        $this->lng = $lng;
        $this->tree = $tree;
        $this->db = $db;
        $this->component_repository = $component_repository;
        $this->testOBJ = $testOBJ;
        $this->access = $access;
        $this->httpState = $httpState;
        $this->refinery = $refinery;

        $this->setId('qpl_brows_tabl_' . $this->testOBJ->getId());
        global $DIC;
        $this->testrequest = $DIC->test()->internal()->request();
        parent::__construct($this, self::CMD_BROWSE_QUESTIONS);
        $this->setFilterCommand(self::CMD_APPLY_FILTER);
        $this->setResetCommand(self::CMD_RESET_FILTER);

        $this->setFormName('questionbrowser');
        $this->setStyle('table', 'fullwidth');
        $this->addColumn('', '', '1%', true);
        $this->addColumn($this->lng->txt("tst_question_title"), 'title', '');
        $this->addColumn($this->lng->txt("description"), 'description', '');
        $this->addColumn($this->lng->txt("tst_question_type"), 'ttype', '');
        $this->addColumn($this->lng->txt("author"), 'author', '');
        $this->addColumn($this->lng->txt('qst_lifecycle'), 'lifecycle', '');
        $this->addColumn($this->lng->txt("create_date"), 'created', '');
        $this->addColumn(
            $this->lng->txt("last_update"),
            'tstamp',
            ''
        );  // name of col is proper "updated" but in data array the key is "tstamp"
        $this->addColumn($this->getParentObjectLabel(), 'qpl', '');
        $this->addColumn($this->lng->txt("working_time"), 'working_time', '');
        $this->setSelectAllCheckbox('q_id');
        $this->setRowTemplate("tpl.il_as_tst_question_browser_row.html", "Modules/Test");

        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject(), $this->getParentCmd()));
        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        $this->enable('sort');
        $this->enable('select_all');
        $this->initFilter();
        $this->setDisableFilterHiding(true);
    }

    public function setWriteAccess(bool $value) : void
    {
        $this->writeAccess = $value;
    }

    public function hasWriteAccess() : bool
    {
        return $this->writeAccess;
    }

    public function init() : void
    {
        if ($this->hasWriteAccess()) {
            $this->addMultiCommand(self::CMD_INSERT_QUESTIONS, $this->lng->txt('insert'));
        }
    }

    public function executeCommand() : bool
    {
        $this->handleParameters();
        $this->handleTabs();

        switch (strtolower((string) $this->ctrl->getNextClass($this))) {
            case strtolower(__CLASS__):
            case '':

                $cmd = $this->ctrl->getCmd() . 'Cmd';
                return $this->$cmd();

            default:

                $this->ctrl->setReturn($this, self::CMD_BROWSE_QUESTIONS);
                return parent::executeCommand();
        }
    }

    private function browseQuestionsCmd() : bool
    {
        $this->setData($this->getQuestionsData());

        $this->mainTpl->setContent($this->ctrl->getHTML($this));
        return true;
    }

    private function applyFilterCmd() : void
    {
        $this->writeFilterToSession();
        $this->ctrl->redirect($this, self::CMD_BROWSE_QUESTIONS);
    }

    private function resetFilterCmd() : void
    {
        $this->resetFilter();
        $this->ctrl->redirect($this, self::CMD_BROWSE_QUESTIONS);
    }

    private function insertQuestionsCmd() : void
    {
        $selected_array = [];
        if ($this->httpState->wrapper()->post()->has('q_id')) {
            $selected_array = $this->httpState->wrapper()->post()->retrieve(
                'q_id',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        if ($selected_array === []) {
            $this->mainTpl->setOnScreenMessage('info', $this->lng->txt("tst_insert_missing_question"), true);
            $this->ctrl->redirect($this, self::CMD_BROWSE_QUESTIONS);
        }

        $testQuestionSetConfig = $this->buildTestQuestionSetConfig();

        $manscoring = false;

        foreach ($selected_array as $key => $value) {
            $last_question_id = $this->testOBJ->insertQuestion($testQuestionSetConfig, $value);

            if (!$manscoring) {
                $manscoring |= assQuestion::_needsManualScoring($value);
            }
        }

        $this->testOBJ->saveCompleteStatus($testQuestionSetConfig);

        if ($manscoring) {
            $this->mainTpl->setOnScreenMessage('info', $this->lng->txt("manscoring_hint"), true);
        } else {
            $this->mainTpl->setOnScreenMessage('success', $this->lng->txt("tst_questions_inserted"), true);
        }

        $this->ctrl->redirectByClass($this->getBackTargetCmdClass(), $this->getBackTargetCommand());
    }

    private function handleParameters() : void
    {
        if ($this->testrequest->isset(self::CONTEXT_PARAMETER)) {
            $this->ctrl->setParameterByClass(
                self::class,
                self::CONTEXT_PARAMETER,
                $this->testrequest->raw(self::CONTEXT_PARAMETER)
            );
            $this->addHiddenInput(self::CONTEXT_PARAMETER, $this->testrequest->raw(self::CONTEXT_PARAMETER));
        }

        if ($this->testrequest->isset(self::MODE_PARAMETER)) {
            $this->ctrl->setParameterByClass(
                self::class,
                self::MODE_PARAMETER,
                $this->testrequest->raw(self::MODE_PARAMETER)
            );
            $this->addHiddenInput(self::MODE_PARAMETER, $this->testrequest->raw(self::MODE_PARAMETER));
        }
    }

    /**
     * @return mixed|null
     */
    private function fetchContextParameter()
    {
        if ($this->testrequest->isset(self::CONTEXT_PARAMETER)) {
            return $this->testrequest->raw(self::CONTEXT_PARAMETER);
        }

        return null;
    }

    /**
     * @return mixed|null
     */
    private function fetchModeParameter()
    {
        if ($this->testrequest->isset(self::MODE_PARAMETER)) {
            return $this->testrequest->raw(self::MODE_PARAMETER);
        }

        return null;
    }

    private function handleTabs() : void
    {
        $this->tabs->clearTargets();
        $this->tabs->clearSubTabs();

        $this->tabs->setBackTarget(
            $this->getBackTargetLabel(),
            $this->getBackTargetUrl()
        );

        $this->tabs->addTab(
            'browseQuestions',
            $this->getBrowseQuestionsTabLabel(),
            $this->getBrowseQuestionsTabUrl()
        );
    }

    private function getBackTargetLabel() : string
    {
        return $this->lng->txt('backtocallingtest');
    }

    private function getBackTargetUrl() : string
    {
        return $this->ctrl->getLinkTargetByClass(
            $this->getBackTargetCmdClass(),
            $this->getBackTargetCommand()
        );
    }

    private function getBackTargetCmdClass() : string
    {
        switch ($this->fetchContextParameter()) {
            case self::CONTEXT_PAGE_VIEW:

                return 'ilTestExpressPageObjectGUI';
            case self::CONTEXT_LIST_VIEW:
            default:
                return 'ilObjTestGUI';
        }
    }

    private function getBackTargetCommand() : string
    {
        switch ($this->fetchContextParameter()) {
            case self::CONTEXT_LIST_VIEW:

                return 'questions';

            case self::CONTEXT_PAGE_VIEW:

                return 'showPage';
        }

        return '';
    }

    private function getBrowseQuestionsTabLabel() : string
    {
        switch ($this->fetchModeParameter()) {
            case self::MODE_BROWSE_POOLS:

                return $this->lng->txt('tst_browse_for_qpl_questions');

            case self::MODE_BROWSE_TESTS:

                return $this->lng->txt('tst_browse_for_tst_questions');
        }

        return '';
    }

    private function getBrowseQuestionsTabUrl() : string
    {
        return $this->ctrl->getLinkTarget($this, self::CMD_BROWSE_QUESTIONS);
    }

    public function initFilter() : void
    {
        $ti = new ilTextInputGUI($this->lng->txt("tst_qbt_filter_question_title"), "title");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/(^[^%]+$)|(^$)/is');
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["title"] = $ti->getValue();

        $ti = new ilTextInputGUI($this->lng->txt("description"), "description");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/(^[^%]+$)|(^$)/is');
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter["description"] = $ti->getValue();

        $ti = new ilTextInputGUI($this->lng->txt("author"), "author");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->setValidationRegexp('/(^[^%]+$)|(^$)/is');
        $ti->readFromSession();
        $this->filter["author"] = $ti->getValue();

        $lifecycleOptions = array_merge(
            ['' => $this->lng->txt('qst_lifecycle_filter_all')],
            ilAssQuestionLifecycle::getDraftInstance()->getSelectOptions($this->lng)
        );
        $lifecycleInp = new ilSelectInputGUI($this->lng->txt('qst_lifecycle'), 'lifecycle');
        $lifecycleInp->setOptions($lifecycleOptions);
        $this->addFilterItem($lifecycleInp);
        $lifecycleInp->readFromSession();
        $this->filter['lifecycle'] = $lifecycleInp->getValue();

        $types = ilObjQuestionPool::_getQuestionTypes();
        $options = [];
        $options[""] = $this->lng->txt('filter_all_question_types');
        foreach ($types as $translation => $row) {
            $options[$row['type_tag']] = $translation;
        }

        $si = new ilSelectInputGUI($this->lng->txt("question_type"), "type");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();
        $this->filter["type"] = $si->getValue();

        $ti = new ilTextInputGUI($this->getParentObjectLabel(), 'parent_title');
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $ti->setValidationRegexp('/(^[^%]+$)|(^$)/is');
        $this->addFilterItem($ti);
        $ti->readFromSession();
        $this->filter['parent_title'] = $ti->getValue();

        $ri = new ilRepositorySelectorInputGUI($this->lng->txt('repository'), 'repository_root_node');
        $ri->setHeaderMessage($this->lng->txt('question_browse_area_info'));
        if ($this->fetchModeParameter() === self::MODE_BROWSE_TESTS) {
            $ri->setClickableTypes(['tst']);
        } else {
            $ri->setClickableTypes(['qpl']);
        }
        $this->addFilterItem($ri);
        $ri->readFromSession();
        $this->filter['repository_root_node'] = $ri->getValue();
    }

    private function getParentObjectLabel() : string
    {
        switch ($this->fetchModeParameter()) {
            case self::MODE_BROWSE_POOLS:

                return $this->lng->txt('qpl');

            case self::MODE_BROWSE_TESTS:

                return $this->lng->txt('tst');
        }

        return '';
    }

    protected function getTranslatedLifecycle(?string $lifecycle) : string
    {
        try {
            return ilAssQuestionLifecycle::getInstance($lifecycle)->getTranslation($this->lng);
        } catch (ilTestQuestionPoolInvalidArgumentException $e) {
            return '';
        }
    }

    public function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("QUESTION_ID", $a_set["question_id"]);
        $this->tpl->setVariable("QUESTION_TITLE", $a_set["title"]);
        $this->tpl->setVariable("QUESTION_COMMENT", $a_set["description"]);
        $this->tpl->setVariable("QUESTION_TYPE", assQuestion::_getQuestionTypeName($a_set["type_tag"]));
        $this->tpl->setVariable("QUESTION_AUTHOR", $a_set["author"]);
        $this->tpl->setVariable("QUESTION_LIFECYCLE", $this->getTranslatedLifecycle($a_set['lifecycle']));
        $this->tpl->setVariable(
            "QUESTION_CREATED",
            ilDatePresentation::formatDate(new ilDate($a_set['created'], IL_CAL_UNIX))
        );
        $this->tpl->setVariable(
            "QUESTION_UPDATED",
            ilDatePresentation::formatDate(new ilDate($a_set["tstamp"], IL_CAL_UNIX))
        );
        $this->tpl->setVariable("QUESTION_POOL", $a_set['parent_title']);
        $this->tpl->setVariable("WORKING_TIME", $a_set['working_time']);
    }

    private function buildTestQuestionSetConfig() : ilTestQuestionSetConfig
    {
        $testQuestionSetConfigFactory = new ilTestQuestionSetConfigFactory(
            $this->tree,
            $this->db,
            $this->component_repository,
            $this->testOBJ
        );

        return $testQuestionSetConfigFactory->getQuestionSetConfig();
    }

    private function getQuestionsData() : array
    {
        $questionList = new ilAssQuestionList($this->db, $this->lng, $this->component_repository);

        $questionList->setQuestionInstanceTypeFilter($this->getQuestionInstanceTypeFilter());
        $questionList->setExcludeQuestionIdsFilter($this->testOBJ->getExistingQuestions());

        $repositoryRootNode = self::REPOSITORY_ROOT_NODE_ID;

        foreach ($this->getFilterItems() as $item) {
            if (!in_array($item->getValue(), [false, null, ''], true)) {
                switch ($item->getPostVar()) {
                    case 'title':
                    case 'description':
                    case 'author':
                    case 'lifecycle':
                    case 'type':
                    case 'parent_title':
                        $questionList->addFieldFilter($item->getPostVar(), $item->getValue());
                        break;

                    case 'repository_root_node':
                        $repositoryRootNode = (int) $item->getValue();
                }
            }
        }
        if ($repositoryRootNode < 1) {
            $repositoryRootNode = self::REPOSITORY_ROOT_NODE_ID;
        }

        $parentObjectIds = $this->getQuestionParentObjIds($repositoryRootNode);

        if (!count($parentObjectIds)) {
            return [];
        }

        $questionList->setParentObjIdsFilter($parentObjectIds);

        $questionList->load();

        return $questionList->getQuestionDataArray();
    }

    private function getQuestionInstanceTypeFilter() : string
    {
        if ($this->fetchModeParameter() === self::MODE_BROWSE_TESTS) {
            return ilAssQuestionList::QUESTION_INSTANCE_TYPE_DUPLICATES;
        }

        return ilAssQuestionList::QUESTION_INSTANCE_TYPE_ORIGINALS;
    }

    /**
     * @param int $repositoryRootNode
     * @return int[]
     */
    private function getQuestionParentObjIds(int $repositoryRootNode) : array
    {
        $parents = $this->tree->getSubTree(
            $this->tree->getNodeData($repositoryRootNode),
            true,
            [$this->getQuestionParentObjectType()]
        );

        $parentIds = [];

        foreach ($parents as $nodeData) {
            if ((int) $nodeData['obj_id'] === $this->testOBJ->getId()) {
                continue;
            }

            $parentIds[$nodeData['obj_id']] = $nodeData['obj_id'];
        }

        $parentIds = array_map('intval', array_values($parentIds));

        if ($this->fetchModeParameter() === self::MODE_BROWSE_POOLS) {
            $available_pools = array_map('intval', array_keys(ilObjQuestionPool::_getAvailableQuestionpools(true)));
            return array_intersect($parentIds, $available_pools);
        } elseif ($this->fetchModeParameter() === self::MODE_BROWSE_TESTS) {
            return array_filter($parentIds, function ($obj_id) : bool {
                $refIds = ilObject::_getAllReferences($obj_id);
                $refId = current($refIds);
                return $this->access->checkAccess('write', '', $refId);
            });
        }

        // Return no parent ids if the user wants to hack...
        return [];
    }

    private function getQuestionParentObjectType() : string
    {
        if ($this->fetchModeParameter() === self::MODE_BROWSE_TESTS) {
            return 'tst';
        }

        return 'qpl';
    }
}
