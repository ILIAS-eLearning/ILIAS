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
	/**
	 * @var ilCtrl
	 */
	protected $ctrl = null;

	/**
	 * @var ilLanguage
	 */
	protected $lng = null;

	/**
	 * @var boolean
	 */
	private $questionAmountColumnEnabled = null;


	public function __construct(ilCtrl $ctrl, ilLanguage $lng, $parentGUI, $parentCMD)
	{
		$this->ctrl = $ctrl;
		$this->lng = $lng;

		parent::__construct($parentGUI, $parentCMD);
	}

	public function setQuestionAmountColumnEnabled($questionAmountColumnEnabled)
	{
		$this->questionAmountColumnEnabled = $questionAmountColumnEnabled;
	}

	public function getQuestionAmountColumnEnabled()
	{
		return $this->questionAmountColumnEnabled;
	}

	public function fillRow($set)
	{
		$this->tpl->setCurrentBlock('col_selection_checkbox');
		$this->tpl->setVariable('SELECTION_CHECKBOX_HTML', $this->getSelectionCheckboxHTML($set['def_id']));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('col_actions');
		$this->tpl->setVariable('ACTIONS_HTML', $this->getActionsHTML($set['def_id']));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock('col_question_amount');
		$this->tpl->setVariable('QUESTION_AMOUNT_INPUT_HTML', $this->getQuestionAmountInputHTML(
			$set['def_id'], $set['question_amount']
		));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable('SOURCE_POOL_LABEL', $set['source_pool_label']);
		$this->tpl->setVariable('FILTER_TAXONOMY', $set['filter_taxonomy']);
		$this->tpl->setVariable('FILTER_TAX_NODE', $set['filter_tax_node']);
	}

	private function getSelectionCheckboxHTML($sourcePoolDefinitionId)
	{
		return '<input type="checkbox" value="'.$sourcePoolDefinitionId.'" name="def_id[]" />';
	}

	private function getQuestionAmountInputHTML($srcPoolDefId, $questionAmount)
	{
		return '<input type="text" size="4" value="'.$questionAmount.'" name="quest_amount['.$srcPoolDefId.']" />';
	}

	private function getActionsHTML($sourcePoolDefinitionId)
	{
		require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';

		$selectionList = new ilAdvancedSelectionListGUI();

		$selectionList->setId('sourcePoolDefinitionActions_'.$sourcePoolDefinitionId);
		$selectionList->setListTitle($this->lng->txt("actions"));

		$selectionList->addItem($this->lng->txt('edit'), '', $this->getEditHref($sourcePoolDefinitionId));
		$selectionList->addItem($this->lng->txt('delete'), '', $this->getDeleteHref($sourcePoolDefinitionId));

		return $selectionList->getHTML();
	}

	private function getEditHref($sourcePoolDefinitionId)
	{
		$href = $this->ctrl->getLinkTarget(
			$this->parent_obj, ilTestRandomQuestionSetConfigGUI::CMD_SHOW_EDIT_SRC_POOL_DEF_FORM
		);

		$href = ilUtil::appendUrlParameterString($href, "def_id=".$sourcePoolDefinitionId, true);

		return $href;
	}

	private function getDeleteHref($sourcePoolDefinitionId)
	{
		$href = $this->ctrl->getLinkTarget(
			$this->parent_obj, ilTestRandomQuestionSetConfigGUI::CMD_DELETE_SINGLE_SRC_POOL_DEF
		);

		$href = ilUtil::appendUrlParameterString($href, "def_id=".$sourcePoolDefinitionId, true);

		return $href;
	}

	public function build()
	{
		$this->setId('sourceQuestionPoolDefinitionListTable');
		$this->setPrefix('sourceQuestionPoolDefinitionListTable');

		$this->setRowTemplate("tpl.il_tst_rnd_quest_set_src_pool_def_row.html", "Modules/Test");

		$this->enable('header');
		$this->enable('select_all');
		$this->disable('sort');

		$this->setExternalSegmentation(true);
		$this->setLimit(PHP_INT_MAX);

		$this->setFormName('sourceQuestionPoolDefinition');
		$this->setFormAction($this->ctrl->getFormAction($this->parent_obj));

		$this->addMultiCommand(ilTestRandomQuestionSetConfigGUI::CMD_DELETE_MULTI_SRC_POOL_DEFS, $this->lng->txt('delete'));
		$this->addCommandButton(ilTestRandomQuestionSetConfigGUI::CMD_SAVE_SRC_POOL_DEF_LIST, $this->lng->txt('save'));

		$this->setTitle($this->lng->txt('tst_src_quest_pool_def_list_table'));

		$this->addColumn('','','1%', true);
		$this->addColumn($this->lng->txt("tst_source_question_pool"),'source_question_pool', '');
		$this->addColumn($this->lng->txt("tst_filter_taxonomy"),'tst_filter_taxonomy', '');
		$this->addColumn($this->lng->txt("tst_filter_tax_node"),'tst_filter_tax_node', '');
		$this->addColumn($this->lng->txt("tst_question_amount"),'tst_question_amount', '');
		$this->addColumn($this->lng->txt("actions"),'actions', '');
	}

	public function init(ilTestRandomQuestionSetSourcePoolDefinitionList $sourcePoolDefinitionList)
	{
		$rows = array();

		foreach($sourcePoolDefinitionList as $sourcePoolDefinition)
		{
			$set = array();

			$set['def_id'] = $sourcePoolDefinition->getPoolId();
			$set['source_pool_label'] = $sourcePoolDefinition->getPoolTitle();
			$set['filter_taxonomy'] = $sourcePoolDefinition->getFilterTaxId();
			$set['filter_tax_node'] = $sourcePoolDefinition->getFilterTaxNodeId();
			$set['question_amount'] = $sourcePoolDefinition->getQuestionAmount();

			$rows[] = $set;
		}

		$this->setData($rows);
	}
}
