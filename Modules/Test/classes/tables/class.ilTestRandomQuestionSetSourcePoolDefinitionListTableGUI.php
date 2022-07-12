<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Table/classes/class.ilTable2GUI.php';

/**
 *
 * @author	Björn Heyser <bheyser@databay.de>
 * @version	$Id$
 *
 * @package	Modules/Test
 */
class ilTestRandomQuestionSetSourcePoolDefinitionListTableGUI extends ilTable2GUI
{
    const IDENTIFIER = 'tstRndPools';
    private bool $definitionEditModeEnabled;
    private bool $questionAmountColumnEnabled;
    private bool $showMappedTaxonomyFilter = false;
    private ?ilTestTaxonomyFilterLabelTranslater $taxonomyLabelTranslater = null;
    private \ILIAS\Test\InternalRequestService $testrequest;

    public function __construct(ilCtrl $ctrl, ilLanguage $lng, $parentGUI, $parentCMD)
    {
        parent::__construct($parentGUI, $parentCMD);

        $this->ctrl = $ctrl;
        $this->lng = $lng;
        global $DIC;
        $this->testrequest = $DIC->test()->internal()->request();
        $this->definitionEditModeEnabled = false;
        $this->questionAmountColumnEnabled = false;
    }

    public function setTaxonomyFilterLabelTranslater(ilTestTaxonomyFilterLabelTranslater $translater) : void
    {
        $this->taxonomyLabelTranslater = $translater;
    }

    public function setDefinitionEditModeEnabled($definitionEditModeEnabled) : void
    {
        $this->definitionEditModeEnabled = $definitionEditModeEnabled;
    }

    public function isDefinitionEditModeEnabled() : bool
    {
        return $this->definitionEditModeEnabled;
    }

    public function setQuestionAmountColumnEnabled(bool $questionAmountColumnEnabled) : void
    {
        $this->questionAmountColumnEnabled = $questionAmountColumnEnabled;
    }

    public function isQuestionAmountColumnEnabled() : bool
    {
        return $this->questionAmountColumnEnabled;
    }
    
    public function setShowMappedTaxonomyFilter(bool $showMappedTaxonomyFilter) : void
    {
        $this->showMappedTaxonomyFilter = $showMappedTaxonomyFilter;
    }

    public function fillRow(array $a_set) : void
    {
        if ($this->isDefinitionEditModeEnabled()) {
            $this->tpl->setCurrentBlock('col_selection_checkbox');
            $this->tpl->setVariable('SELECTION_CHECKBOX_HTML', $this->getSelectionCheckboxHTML($a_set['def_id']));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('col_actions');
            $this->tpl->setVariable('ACTIONS_HTML', $this->getActionsHTML($a_set['def_id']));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock('col_order_checkbox');
            $this->tpl->setVariable('ORDER_INPUT_HTML', $this->getDefinitionOrderInputHTML(
                $a_set['def_id'],
                $this->getOrderNumberForSequencePosition($a_set['sequence_position'])
            ));
            $this->tpl->parseCurrentBlock();
        }
        // fau: taxFilter/typeFilter - show sequence position to identify the filter in the database
        else {
            $this->tpl->setCurrentBlock('col_order_checkbox');
            $this->tpl->setVariable('ORDER_INPUT_HTML', $a_set['sequence_position']);
            $this->tpl->parseCurrentBlock();
        }
        // fau.

        if ($this->isQuestionAmountColumnEnabled()) {
            if ($this->isDefinitionEditModeEnabled()) {
                $questionAmountHTML = $this->getQuestionAmountInputHTML(
                    $a_set['def_id'],
                    $a_set['question_amount']
                );
            } else {
                $questionAmountHTML = $a_set['question_amount'];
            }

            $this->tpl->setCurrentBlock('col_question_amount');
            $this->tpl->setVariable('QUESTION_AMOUNT_INPUT_HTML', $questionAmountHTML);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable('SOURCE_POOL_LABEL', $a_set['source_pool_label']);
        // fau: taxFilter/typeFilter - set taxonomy/type filter label in a single coulumn each
        $this->tpl->setVariable('TAXONOMY_FILTER', $this->taxonomyLabelTranslater->getTaxonomyFilterLabel($a_set['taxonomy_filter'], '<br />'));
        #$this->tpl->setVariable('FILTER_TAXONOMY', $this->getTaxonomyTreeLabel($set['filter_taxonomy']));
        #$this->tpl->setVariable('FILTER_TAX_NODE', $this->getTaxonomyNodeLabel($set['filter_tax_node']));
        $this->tpl->setVariable('LIFECYCLE_FILTER', $this->taxonomyLabelTranslater->getLifecycleFilterLabel($a_set['lifecycle_filter']));
        $this->tpl->setVariable('TYPE_FILTER', $this->taxonomyLabelTranslater->getTypeFilterLabel($a_set['type_filter']));
        // fau.
    }

    private function getSelectionCheckboxHTML($sourcePoolDefinitionId) : string
    {
        return '<input type="checkbox" value="' . $sourcePoolDefinitionId . '" name="src_pool_def_ids[]" />';
    }

    private function getDefinitionOrderInputHTML($srcPoolDefId, $defOrderNumber) : string
    {
        return '<input type="text" size="2" value="' . $defOrderNumber . '" name="def_order[' . $srcPoolDefId . ']" />';
    }

    private function getQuestionAmountInputHTML($srcPoolDefId, $questionAmount) : string
    {
        return '<input type="text" size="4" value="' . $questionAmount . '" name="quest_amount[' . $srcPoolDefId . ']" />';
    }

    private function getActionsHTML($sourcePoolDefinitionId) : string
    {
        require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

        $selectionList = new ilAdvancedSelectionListGUI();

        $selectionList->setId('sourcePoolDefinitionActions_' . $sourcePoolDefinitionId);
        $selectionList->setListTitle($this->lng->txt("actions"));

        $selectionList->addItem($this->lng->txt('edit'), '', $this->getEditHref($sourcePoolDefinitionId));
        $selectionList->addItem($this->lng->txt('delete'), '', $this->getDeleteHref($sourcePoolDefinitionId));

        return $selectionList->getHTML();
    }

    private function getEditHref($sourcePoolDefinitionId) : string
    {
        $href = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilTestRandomQuestionSetConfigGUI::CMD_SHOW_EDIT_SRC_POOL_DEF_FORM
        );

        $href = ilUtil::appendUrlParameterString($href, "src_pool_def_id=" . $sourcePoolDefinitionId, true);

        return $href;
    }

    private function getDeleteHref($sourcePoolDefinitionId) : string
    {
        $href = $this->ctrl->getLinkTarget(
            $this->parent_obj,
            ilTestRandomQuestionSetConfigGUI::CMD_DELETE_SINGLE_SRC_POOL_DEF
        );

        $href = ilUtil::appendUrlParameterString($href, "src_pool_def_id=" . $sourcePoolDefinitionId, true);

        return $href;
    }

    private function getOrderNumberForSequencePosition($sequencePosition)
    {
        return ($sequencePosition * 10);
    }

    private function getTaxonomyTreeLabel($taxonomyTreeId)
    {
        if (!$taxonomyTreeId) {
            return '';
        }

        return $this->taxonomyLabelTranslater->getTaxonomyTreeLabel($taxonomyTreeId);
    }

    private function getTaxonomyNodeLabel($taxonomyNodeId)
    {
        if (!$taxonomyNodeId) {
            return '';
        }

        return $this->taxonomyLabelTranslater->getTaxonomyNodeLabel($taxonomyNodeId);
    }

    public function build() : void
    {
        $this->setTableIdentifiers();

        $this->setTitle($this->lng->txt('tst_src_quest_pool_def_list_table'));

        $this->setRowTemplate("tpl.il_tst_rnd_quest_set_src_pool_def_row.html", "Modules/Test");

        $this->enable('header');
        $this->disable('sort');

        $this->enable('select_all');
        $this->setSelectAllCheckbox('src_pool_def_ids[]');

        $this->setExternalSegmentation(true);
        $this->setLimit(PHP_INT_MAX);

        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));

        $this->addCommands();
        $this->addColumns();
    }

    private function setTableIdentifiers() : void
    {
        $this->setId(self::IDENTIFIER);
        $this->setPrefix(self::IDENTIFIER);
        $this->setFormName(self::IDENTIFIER);
    }

    private function addCommands() : void
    {
        if ($this->isDefinitionEditModeEnabled()) {
            $this->addMultiCommand(ilTestRandomQuestionSetConfigGUI::CMD_DELETE_MULTI_SRC_POOL_DEFS, $this->lng->txt('delete'));
            $this->addCommandButton(ilTestRandomQuestionSetConfigGUI::CMD_SAVE_SRC_POOL_DEF_LIST, $this->lng->txt('save'));
        }
    }

    private function addColumns() : void
    {
        if ($this->isDefinitionEditModeEnabled()) {
            $this->addColumn('', 'select', '1%', true);
            $this->addColumn('', 'order', '1%', true);
        } else {
            $this->addColumn($this->lng->txt("position"));
        }

        $this->addColumn($this->lng->txt("tst_source_question_pool"), 'source_question_pool', '');
        $this->addColumn($this->lng->txt("tst_filter_taxonomy") . ' / ' . $this->lng->txt("tst_filter_tax_node"), 'tst_filter_taxonomy', '');
        #$this->addColumn($this->lng->txt("tst_filter_taxonomy"),'tst_filter_taxonomy', '');
        #$this->addColumn($this->lng->txt("tst_filter_tax_node"),'tst_filter_tax_node', '');
        $this->addColumn($this->lng->txt("qst_lifecycle"), 'tst_filter_lifecycle', '');
        $this->addColumn($this->lng->txt("tst_filter_question_type"), 'tst_filter_question_type', '');

        if ($this->isQuestionAmountColumnEnabled()) {
            $this->addColumn($this->lng->txt("tst_question_amount"), 'tst_question_amount', '');
        }

        if ($this->isDefinitionEditModeEnabled()) {
            $this->addColumn($this->lng->txt("actions"), 'actions', '');
        }
    }

    public function init(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList) : void
    {
        $rows = array();

        foreach ($sourcePoolDefinitionList as $sourcePoolDefinition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $sourcePoolDefinition */

            $set = array();

            $set['def_id'] = $sourcePoolDefinition->getId();
            $set['sequence_position'] = $sourcePoolDefinition->getSequencePosition();
            $set['source_pool_label'] = $sourcePoolDefinition->getPoolTitle();
            // fau: taxFilter/typeFilter - get the type and taxonomy filter for display
            if ($this->showMappedTaxonomyFilter) {
                // mapped filter will be used after synchronisation
                $set['taxonomy_filter'] = $sourcePoolDefinition->getMappedTaxonomyFilter();
            } else {
                // original filter will be used before synchronisation
                $set['taxonomy_filter'] = $sourcePoolDefinition->getOriginalTaxonomyFilter();
            }
            #$set['filter_taxonomy'] = $sourcePoolDefinition->getMappedFilterTaxId();
            #$set['filter_tax_node'] = $sourcePoolDefinition->getMappedFilterTaxNodeId();
            $set['lifecycle_filter'] = $sourcePoolDefinition->getLifecycleFilter();
            $set['type_filter'] = $sourcePoolDefinition->getTypeFilter();
            // fau.
            $set['question_amount'] = $sourcePoolDefinition->getQuestionAmount();

            $rows[] = $set;
        }

        $this->setData($rows);
    }

    public function applySubmit(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList) : void
    {
        foreach ($sourcePoolDefinitionList as $sourcePoolDefinition) {
            /** @var ilTestRandomQuestionSetSourcePoolDefinition $sourcePoolDefinition */

            $orderNumber = $this->fetchOrderNumberParameter($sourcePoolDefinition);
            $sourcePoolDefinition->setSequencePosition($orderNumber);

            if ($this->isQuestionAmountColumnEnabled()) {
                $questionAmount = $this->fetchQuestionAmountParameter($sourcePoolDefinition);
                $sourcePoolDefinition->setQuestionAmount($questionAmount);
            } else {
                $sourcePoolDefinition->setQuestionAmount(null);
            }
        }
    }

    private function fetchOrderNumberParameter(ilTestRandomQuestionSetSourcePoolDefinition $definition) : int
    {
        $def_order = $this->testrequest->raw('def_order');
        return array_key_exists($definition->getId(), $def_order) ? (int) $def_order[$definition->getId()] : 0;
    }

    private function fetchQuestionAmountParameter(ilTestRandomQuestionSetSourcePoolDefinition $definition) : int
    {
        $quest_amount = $this->testrequest->raw('quest_amount');
        return array_key_exists($definition->getId(), $quest_amount) ? (int) $quest_amount[$definition->getId()] : 0;
    }
}
