<?php

/**
 * Class ilBiblFieldFilterTableGUI
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblFieldFilterTableGUI extends ilTable2GUI {

	const TBL_ID = 'tbl_bibl_filters';
	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	/**
	 * @var \ilBiblFieldFilterGUI
	 */
	protected $parent_obj;
	/**
	 * @var ilObjBibliographic
	 */
	protected $il_obj_bibliographic;
	/**
	 * @var array
	 */
	protected $filter = [];


	/**
	 * ilBiblFieldFilterTableGUI constructor.
	 *
	 * @param \ilBiblFieldFilterGUI $a_parent_obj
	 * @param string                $a_parent_cmd
	 * @param \ilObjBibliographic   $il_obj_bibliographic
	 */
	function __construct(\ilBiblFieldFilterGUI $a_parent_obj, $a_parent_cmd, \ilObjBibliographic $il_obj_bibliographic) {
		global $DIC;
		$this->dic = $DIC;
		$this->parent_obj = $a_parent_obj;
		$this->ctrl = $this->dic->ctrl();
		$this->tpl = $this->dic['tpl'];

		$this->setId(self::TBL_ID);
		$this->setPrefix(self::TBL_ID);
		$this->setFormName(self::TBL_ID);
		$this->ctrl->saveParameter($a_parent_obj, $this->getNavParameter());
		$this->il_obj_bibliographic = $il_obj_bibliographic;

		$this->initButtons();

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->parent_obj = $a_parent_obj;
		$this->setRowTemplate('tpl.bibl_settings_filters_list_row.html', 'Modules/Bibliographic');

		$this->setFormAction($this->ctrl->getFormActionByClass(ilBiblFieldFilterGUI::class));
		$this->setExternalSorting(true);

		$this->setDefaultOrderField("identifier");
		$this->setDefaultOrderDirection("asc");
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);

		$this->initColumns();
		$this->addFilterItems();
		$this->parseData();
	}

	protected function initButtons() {
		if ($this->dic->access()->checkAccess('write', "", $this->il_obj_bibliographic->getRefId())) {
			$new_filter_link = $this->ctrl->getLinkTargetByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_ADD);
			$ilLinkButton = ilLinkButton::getInstance();
			$ilLinkButton->setCaption($this->dic->language()->txt("add_filter"), false);
			$ilLinkButton->setUrl($new_filter_link);
			$this->dic->toolbar()->addButtonInstance($ilLinkButton);
		}
	}

	protected function initColumns() {
		$this->addColumn($this->dic->language()->txt('field'), 'field');
		$this->addColumn($this->dic->language()->txt('filter_type'), 'filter_type');
		$this->addColumn($this->dic->language()->txt('actions'), '', '150px');
	}

	protected function addFilterItems() {
		$field = new ilTextInputGUI($this->dic->language()->txt('field'), 'field');
		$this->addAndReadFilterItem($field);
	}

	/**
	 * @param $field
	 */
	protected function addAndReadFilterItem(ilFormPropertyGUI $field) {
		$this->addFilterItem($field);
		$field->readFromSession();
		if ($field instanceof ilCheckboxInputGUI) {
			$this->filter[$field->getPostVar()] = $field->getChecked();
		} else {
			$this->filter[$field->getPostVar()] = $field->getValue();
		}
	}

	/**
	 * Fills table rows with content from $a_set.
	 *
	 * @param array    $a_set
	 */
	public function fillRow($a_set) {
		/**
		 * @var ilBiblField $ilField
		 */
		$ilField = ilBiblField::find($a_set['id']);
		$this->tpl->setCurrentBlock("FIELD");
		$this->tpl->setVariable('VAL_FIELD', $a_set['field']);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("FILTER_TYPE");
		$this->tpl->setVariable('VAL_FILTER_TYPE', $a_set['filter_type']);
		$this->tpl->parseCurrentBlock();
		$this->addActionMenu($ilField);
	}

	/**
	 * @param ilBiblField $ilField
	 */
	protected function addActionMenu(ilBiblField $ilField) {
		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->lng->txt("actions"));
		$current_selection_list->setId($ilField->getId());
		$current_selection_list->addItem($this->dic->language()->txt("edit"), "", $this->dic->ctrl()->getLinkTargetByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_EDIT));
		$current_selection_list->addItem($this->dic->language()->txt("delete"), "", $this->dic->ctrl()->getLinkTargetByClass(ilBiblFieldFilterGUI::class, ilBiblFieldFilterGUI::CMD_DELETE));
		$this->tpl->setVariable('VAL_ACTIONS', $current_selection_list->getHTML());
	}

	protected function parseData() {
		$this->determineOffsetAndOrder();
		$this->determineLimit();

		$collection = ilBiblField::getCollection();
		$collection->where(array( 'object_id' => $this->il_obj_bibliographic->getId() ));

		$sorting_column = $this->getOrderField() ? $this->getOrderField() : 'field';
		$offset = $this->getOffset() ? $this->getOffset() : 0;

		$sorting_direction = $this->getOrderDirection();
		$num = $this->getLimit();

		$collection->orderBy($sorting_column, $sorting_direction);
		$collection->limit($offset, $num);

		foreach ($this->filter as $filter_key => $filter_value) {
			switch ($filter_key) {
				case 'identifier':
					$collection->where(array( $filter_key => '%' . $filter_value . '%' ), 'LIKE');
					break;
			}
		}
		$this->setData($collection->getArray());
	}

}