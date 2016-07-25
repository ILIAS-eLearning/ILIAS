<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once ('./Services/Table/classes/class.ilTable2GUI.php');
require_once ('./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php');
/**
 * Class ilDclTableListTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclTableListTableGUI extends ilTable2GUI{

	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilDclTableListGUI
	 */
	protected $parent_obj;


	/**
	 * ilDclTableListTableGUI constructor.
	 */
	public function __construct($parent_obj) {
		global $DIC;
		$lng = $DIC['lng'];
		$ilCtrl = $DIC['ilCtrl'];

		parent::__construct($parent_obj);

		$this->parent_obj = $parent_obj;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;

		$this->setId('dcl_table_list');
		$this->addColumn('', '', '1', true);
		$this->addColumn($lng->txt('dcl_order'), NULL, '30px');
		$this->addColumn($lng->txt('title'), NULL, 'auto');
		$this->addColumn($lng->txt('dcl_visible'), NULL, '200px', false, '', $this->lng->txt('dcl_visible_tooltip'));
		$this->addColumn($lng->txt('dcl_comments'), NULL, '100px', false, '', $this->lng->txt('dcl_public_comments'));
		$this->addColumn($lng->txt('actions'), NULL, '30px');

		$this->setSelectAllCheckbox('dcl_table_ids[]');
		$this->addMultiCommand('confirmDeleteTables', $lng->txt('dcl_delete_tables'));

		$this->setFormAction($ilCtrl->getFormActionByClass('ildcltablelistgui'));
		$this->addCommandButton('save', $lng->txt('dcl_save'));

		$this->setFormAction($ilCtrl->getFormAction($parent_obj));
		$this->setFormName('table_list');

		//those two are important as we get our data as objects not as arrays.
		$this->setExternalSegmentation(true);
		$this->setExternalSorting(true);

		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setShowRowsSelector(false);
		$this->setShowTemplates(false);
		$this->setEnableHeader(true);
		$this->setEnableTitle(true);
		$this->setDefaultOrderDirection('asc');

		$this->setTitle($lng->txt('dcl_table_list_tables'));
		$this->setRowTemplate('tpl.table_list_row.html', 'Modules/DataCollection');
		$this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');

		$tables = $this->parent_obj->getDataCollectionObject()->getTables();
		$this->setData($tables);
	}

	/**
	 * @param ilDclTable $a_set
	 */
	public function fillRow($a_set)
	{
		$this->tpl->setVariable("ID", $a_set->getId());
		$this->tpl->setVariable("ORDER_NAME", "order[{$a_set->getId()}]");
		$this->tpl->setVariable("ORDER_VALUE", $a_set->getOrder());
		$this->tpl->setVariable("TITLE", $a_set->getTitle());
		$this->tpl->setVariable("CHECKBOX_NAME_VISIBLE", 'visible[' . $a_set->getId() . ']');
		if ($a_set->getIsVisible()) {
			$this->tpl->setVariable("CHECKBOX_CHECKED_VISIBLE", 'checked');
		}
		$this->tpl->setVariable("CHECKBOX_NAME_COMMENTS", 'comments[' . $a_set->getId() . ']');
		if ($a_set->getPublicCommentsEnabled()) {
			$this->tpl->setVariable("CHECKBOX_CHECKED_COMMENTS", 'checked');
		}
		$this->tpl->setVariable('ACTIONS', $this->buildActions($a_set->getId()));
	}

	/**
	 * build actions menu
	 * @param $id
	 * @return string
	 */
	protected function buildActions($id) {

			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId($id);
			$alist->setListTitle($this->lng->txt('actions'));
			$this->ctrl->setParameterByClass('ildclfieldlistgui', 'table_id', $id);
			$this->ctrl->setParameterByClass('ildcltableviewgui', 'table_id', $id);
			$this->ctrl->setParameterByClass('ildcltableeditgui', 'table_id', $id);
			$alist->addItem($this->lng->txt('settings'), '', $this->ctrl->getLinkTargetByClass('ildcltableeditgui', 'edit'));
			$alist->addItem($this->lng->txt('dcl_list_fields'), '', $this->ctrl->getLinkTargetByClass('ildclfieldlistgui', 'listFields'));
			$alist->addItem($this->lng->txt('dcl_tableviews'), '', $this->ctrl->getLinkTargetByClass('ildcltableviewgui'));
			$alist->addItem($this->lng->txt('delete'), '', $this->ctrl->getLinkTargetByClass('ildcltableeditgui', 'confirmDelete'));
			return $alist->getHTML();

	}
}