<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.srModelObjectTableGUI.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecordList.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Index/class.arIndexTableField.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Index/class.arIndexTableFields.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Index/class.arIndexTableAction.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Index/class.arIndexTableActions.php');

/**
 * GUI-Class arIndexTableGUI
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
 *
 */
class arIndexTableGUI extends ilTable2GUI {

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;
	/**
	 * @var ilAccessHandler
	 */
	protected $access;
	/**
	 * @var arIndexTableActions
	 */
	protected $actions;
	/**
	 * @var arIndexTableActions
	 */
	protected $multi_item_actions = array();
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar = NULL;
	/**
	 * @var string
	 */
	protected $table_title = '';
	/**
	 * arIndexTableFields
	 */
	protected $fields = NULL;
	/**
	 * @var ActiveRecordList|null
	 */
	protected $active_record_list = NULL;
	/**
	 * @var arGUI|null
	 */
	protected $parent_obj = NULL;


	/**
	 * @param arGUI            $a_parent_obj
	 * @param string           $a_parent_cmd
	 * @param ActiveRecordList $active_record_list
	 */
	public function __construct(arGUI $a_parent_obj, $a_parent_cmd, ActiveRecordList $active_record_list) {
		global $ilCtrl, $ilTabs, $ilAccess;
		$this->ctrl = $ilCtrl;
		$this->tabs = $ilTabs;
		$this->access = $ilAccess;

		$this->parent_obj = $a_parent_obj;

		$this->active_record_list = $active_record_list;

		$this->setId(strtolower(get_class($this->active_record_list->getAR()) . "_index"));

		$this->initBeforeParentConstructor();

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->initAfterParentConstructor();
	}


	protected function initBeforeParentConstructor() {
		$this->initFields();
		$this->initActions();
		$this->initMultiItemActions();
		$this->initMultiItemActionsButton();
	}


	protected function initAfterParentConstructor() {
		$this->initFormAction();
		$this->initCommandButtons();
		$this->initToolbar();

		$this->initTableFilter();
		$this->initRowSelector();

		$this->initTableRowTemplate();
		$this->initTableColumns();
		$this->initTableData();
	}


	protected function initTitle() {
		$this->setTableTitle($this->getId());
	}


	protected function initFields() {
		$this->fields = new arIndexTableFields($this->active_record_list->getAR());
		$this->customizeFields();
		$this->fields->sortFields();
	}


	/**
	 * @description To be overridden
	 */
	protected function customizeFields() {
	}


	protected function initActions() {
		global $lng;

		$this->addAction(new arIndexTableAction('view', $lng->txt('view'), get_class($this->parent_obj), 'view', 'view'));
		$this->addAction(new arIndexTableAction('edit', $lng->txt('edit'), get_class($this->parent_obj), 'edit', 'write'));
		$this->addAction(new arIndexTableAction('delete', $lng->txt('delete'), get_class($this->parent_obj), 'delete', 'write'));
	}


	/**
	 * @param arIndexTableAction $action
	 */
	protected function addAction(arIndexTableAction $action) {
		if (!$this->getActions()) {
			$this->setActions(new arIndexTableActions());
		}
		$this->actions->addAction($action);
	}


	protected function initFormAction() {
		$this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
	}


	protected function initCommandButtons() {
	}


	protected function initToolbar() {
		if ($this->getActions() && $this->getActions()->getAction("edit")) {
			$toolbar = new ilToolbarGUI();
			$toolbar->addButton($this->getAddButtonTxt(), $this->ctrl->getLinkTarget($this->parent_obj, "add"));
			$this->setToolbar($toolbar);
		}
	}


	/**
	 * @return string
	 */
	protected function getAddButtonTxt() {
		return $this->txt("add_item");
	}


	protected function initMultiItemActions() {
		if ($this->getActions() && $this->getActions()->getAction("delete")) {
			$this->addMutliItemAction(new arIndexTableAction('delete', $this->getMultiDeleteTxt(), get_class($this->parent_obj), 'delete'));
		}
	}


	/**
	 * @return string
	 */
	protected function getMultiDeleteTxt() {
		return $this->txt("delete", false);
	}


	/**
	 * @param arIndexTableAction $action
	 */
	public function addMutliItemAction(arIndexTableAction $action) {
		if (!$this->getMultiItemActions()) {
			$this->setMultiItemActions(new arIndexTableActions());
		}
		$this->multi_item_actions->addAction($action);
	}


	protected function initMultiItemActionsButton() {
		if ($this->getMultiItemActions()) {
			$this->addMultiItemSelectionButton("index_table_multi_action", $this->multi_item_actions->getActionsAsKeyTextArray(), "multiAction", $this->txt('execute', false));
			$this->setSelectAllCheckbox("id[]");
		}
	}


	/**
	 * Get selectable columns
	 *
	 * @return        array
	 */
	public function getSelectableColumns() {
		return $this->getFields()->getSelectableColumns($this);
	}


	/**
	 *
	 */
	protected function initTableFilter() {
		$this->setFilterCols(7);
		$this->setFilterCommand("applyFilter");
		$this->setResetCommand("resetFilter");

		$fields = $this->getFieldsAsArray();

		foreach ($fields as $field) {
			/**
			 * @var arIndexTableField $field
			 */
			if ($field->getHasFilter()) {
				$this->addFilterField($field);
			}
		}
	}


	/**
	 * @param arIndexTableField $field
	 */
	protected function addFilterField(arIndexTableField $field) {
		switch ($field->getFieldType()) {
			case 'integer':
			case 'float':
				$this->addFilterItemByMetaType($field->getName(), self::FILTER_NUMBER_RANGE, false, $this->txt($field->getTxt()));
				break;
			case 'text':
			case 'clob':
				$this->addFilterItemByMetaType($field->getName(), self::FILTER_TEXT, false, $this->txt($field->getTxt()));
				break;
			case 'date':
				$this->addFilterItemByMetaType($field->getName(), self::FILTER_DATE_RANGE, false, $this->txt($field->getTxt()));
				break;
			case 'time':
			case 'timestamp':
				$this->addFilterItemByMetaType($field->getName(), self::FILTER_DATETIME_RANGE, false, $this->txt($field->getTxt()));
				break;
		}
	}


	/**
	 * @param ilFormPropertyGUI $item
	 * @param bool              $optional
	 */
	protected function addFilterItemToForm(ilFormPropertyGUI $item, $optional = false) {
		/**
		 * @var $item ilFormPropertyGUI
		 */
		$this->addFilterItem($item, $optional);
		$item->readFromSession();
		$this->filter_array[$item->getPostVar()] = $item->getValue();
	}


	protected function initRowSelector() {
		$this->setShowRowsSelector(true);
	}


	/**
	 * @return bool
	 * @description returns false, if dynamic template is needed, otherwise implement your own template by $this->setRowTemplate($a_template, $a_template_dir = '')
	 */
	protected function initTableRowTemplate() {
		$this->setRowTemplate('tpl.record_row.html', './Customizing/global/plugins/Libraries/ActiveRecord/');
	}


	/**
	 * @return bool|void
	 */
	protected function initTableColumns() {
		$this->addMultipleSelectionColumn();

		foreach ($this->getFieldsAsArray() as $field) {
			/**
			 * @var arIndexTableField $field
			 */
			if ($this->checkColumnVisibile($field)) {
				if ($field->getSortable()) {
					$this->addColumn($this->txt($field->getTxt()), $field->getName());
				} else {
					$this->addColumn($this->txt($field->getTxt()));
				}
			}
		}
		if ($this->getActions()) {
			$this->addColumn($this->txt('actions', false));
		}
	}


	protected function addMultipleSelectionColumn() {
		if ($this->getMultiItemActions()) {
			$this->addColumn("", "", 1);
		}
	}


	/**
	 * @param arIndexTableField $field
	 *
	 * @return bool
	 */
	protected function checkColumnVisibile(arIndexTableField $field) {
		return ($field->getVisible() && !$this->getSelectableColumns()) || $this->isColumnSelected($field->getName());
	}


	protected function initTableData() {
		$this->active_record_list->getArWhereCollection()->setStatements(NULL);
		$this->active_record_list->getArJoinCollection()->setStatements(NULL);
		$this->active_record_list->getArLimitCollection()->setStatements(NULL);
		$this->active_record_list->getArOrderCollection()->setStatements(NULL);

		$this->filterTableData();
		$this->beforeGetData();
		$this->setOrderAndSegmentation();
		$ar_data = $this->active_record_list->getArray();
		$data = array();

		foreach ($ar_data as $key => $item) {
			$data[$key] = array();
			foreach ($this->getFields()->getFieldsForDisplay() as $field) {
				/**
				 * @var arIndexTableField $field
				 */
				if (array_key_exists($field->getName(), $item)) {
					if (!$item[$field->getName()]) {
						$data[$key][$field->getName()] = $this->setEmptyFieldData($field, $item);
					} elseif ($field->getIsCreatedByField()) {
						$data[$key][$field->getName()] = $this->setArCreatedByField($field, $item, $item[$field->getName()]);
					} elseif ($field->getIsModifiedByField()) {
						$data[$key][$field->getName()] = $this->setArModifiedByField($field, $item, $item[$field->getName()]);
					} else {
						$data[$key][$field->getName()] = $this->setArFieldData($field, $item, $item[$field->getName()]);
					}
				} else {
					$data[$key][$field->getName()] = $this->setCustomFieldData($field, $item);
				}
			}
		}
		$this->setData($data);
	}


	protected function filterTableData() {
		$filters = $this->getFilterItems();
		if ($filters) {
			foreach ($filters as $filter) {
				/**
				 * @var ilFormPropertyGUI|ilTextInputGUI $filter
				 */
				$this->addFilterWhere($filter, $filter->getPostVar(), $filter->getValue());
			}
		}
	}


	/**
	 * @param ilFormPropertyGUI $filter
	 * @param                   $name
	 * @param                   $value
	 */
	protected function addFilterWhere(ilFormPropertyGUI $filter, $name, $value) {

		switch (get_class($filter)) {
			case 'ilTextInputGUI':
				$this->addFilterTextWhere($filter, $name, $value);

				return;
			case 'ilCombinationInputGUI':
				if (is_object($value["from"]) || is_object($value["to"])) {
					if (get_class($value["from"]) == "ilDateTime" || get_class($value["to"]) == "ilDateTime") {
						$this->addFilterDateTimeWhere($filter, $name, $value);

						return;
					}
					if (get_class($value["from"]) == "ilDate" || get_class($value["to"]) == "ilDate") {
						$this->addFilterDateWhere($filter, $name, $value);

						return;
					}
					$this->addFilterCustomWhere($filter, $name, $value);

					return;
				}

				$this->addFilterNumericWhere($filter, $name, $value);
				break;
			default:
				$this->addFilterCustomWhere($filter, $name, $value);

				return;
		}
	}


	/**
	 * @param ilCombinationInputGUI $filter
	 * @param                       $name
	 * @param                       $value
	 */
	protected function addFilterNumericWhere(ilCombinationInputGUI $filter, $name, $value) {

		if ($value["from"] != "" OR $value["to"] != "") {
			if ($value["from"] == "") {
				$value["from"] = 0;
			}
			if ($value["to"] == "") {
				$value["to"] = PHP_INT_MAX;
			}
			$this->active_record_list->where($this->active_record_list->getAR()->getConnectorContainerName() . "." . $name . " >= " . $value["from"]
				. " AND " . $this->active_record_list->getAR()->getConnectorContainerName() . "." . $name . " <= " . $value["to"]);
		}
	}


	/**
	 * @param ilTextInputGUI $filter
	 * @param                $name
	 * @param                $value
	 */
	protected function addFilterTextWhere(ilTextInputGUI $filter, $name, $value) {
		$this->active_record_list->where($this->active_record_list->getAR()->getConnectorContainerName() . "." . $name . " like '%" . $value . "%'");
	}


	/**
	 * @param ilCombinationInputGUI $filter
	 * @param                       $name
	 * @param                       $value
	 */
	protected function addFilterDateWhere(ilCombinationInputGUI $filter, $name, $value) {
		if ($value["from"] != NULL OR $value["to"] != NULL) {
			if ($value["from"] == NULL) {
				$value["from"] = new ilDateTime("0001-01-01", IL_CAL_DATE);
			}
			if ($value["to"] == NULL) {
				$value["to"] = new ilDateTime("9999-01-01", IL_CAL_DATE);
			}
			$this->active_record_list->where($this->active_record_list->getAR()->getConnectorContainerName() . "." . $name . " BETWEEN '"
				. $value["from"]->get(IL_CAL_DATE) . "' AND '" . $value["to"]->get(IL_CAL_DATE) . "'");
		}
	}


	/**
	 * @param ilCombinationInputGUI $filter
	 * @param                       $name
	 * @param                       $value
	 */
	protected function addFilterDateTimeWhere(ilCombinationInputGUI $filter, $name, $value) {
		if ($value["from"] != NULL OR $value["to"] != NULL) {
			if ($value["from"] == NULL) {
				$value["from"] = new ilDateTime("0001-01-01", IL_CAL_DATE);
			}
			if ($value["to"] == NULL) {
				$value["to"] = new ilDateTime("9999-01-01", IL_CAL_DATE);
			}
			$this->active_record_list->where($this->active_record_list->getAR()->getConnectorContainerName() . "." . $name . " BETWEEN '"
				. $value["from"]->get(IL_CAL_DATETIME) . "' AND '" . $value["to"]->get(IL_CAL_DATETIME) . "'");
		}
	}


	protected function addFilterCustomWhere() {
	}


	protected function beforeGetData() {
	}


	protected function setOrderAndSegmentation() {
		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		if (!$this->getDefaultOrderField()) {
			$this->setDefaultOrderField($this->active_record_list->getAR()->getArFieldList()->getPrimaryField()->getName());
		}
		$this->determineLimit();
		$this->determineOffsetAndOrder();
		$this->setMaxCount($this->active_record_list->count());
		$this->active_record_list->orderBy($this->getOrderField(), $this->getOrderDirection());
		$this->active_record_list->limit($this->getOffset(), $this->getLimit());
	}


	/**
	 * @param arIndexTableField $field
	 * @param                   $item
	 *
	 * @return string
	 */
	protected function setEmptyFieldData(arIndexTableField $field, $item) {
		return "";
	}


	/**
	 * @param arIndexTableField $field
	 * @param                   $item
	 * @param                   $value
	 *
	 * @return string
	 */
	protected function setArModifiedByField(arIndexTableField $field, $item, $value) {
		$user = new ilObjUser($value);

		return $user->getPublicName();
	}


	/**
	 * @param arIndexTableField $field
	 * @param                   $item
	 * @param                   $value
	 *
	 * @return string
	 */
	protected function setArCreatedByField(arIndexTableField $field, $item, $value) {
		$user = new ilObjUser($value);

		return $user->getPublicName();
	}


	/**
	 * @param arIndexTableField $field
	 * @param                   $item
	 * @param                   $value
	 *
	 * @return string
	 */
	protected function setArFieldData(arIndexTableField $field, $item, $value) {
		switch ($field->getFieldType()) {
			case 'integer':
			case 'float':
			case 'text':
			case 'clob':
				return $value;
			case 'date':
			case 'time':
			case 'timestamp':
				return $this->setDateFieldData($field, $item, $value);
		}

		return "";
	}


	/**
	 * @param arIndexTableField $field
	 * @param                   $item
	 * @param                   $value
	 *
	 * @return string
	 */
	protected function setDateFieldData(arIndexTableField $field, $item, $value) {
		$datetime = new ilDateTime($value, IL_CAL_DATETIME);

		return ilDatePresentation::formatDate($datetime, IL_CAL_UNIX);
	}


	/**
	 * @param arIndexTableField $field
	 * @param                   $item
	 *
	 * @return string
	 */
	protected function setCustomFieldData(arIndexTableField $field, $item) {
		return "CUSTOM-OVERRIDE: setCustomFieldData";
	}


	/**
	 * @param array $a_set
	 *
	 * @internal    param array $_set
	 * @description override, when using own columns
	 */
	final function fillRow($a_set) {
		$this->setCtrlParametersForRow($a_set);
		$this->addMultiItemActionCheckboxToRow($a_set);
		$this->parseRow($a_set);
		$this->addActionsToRow($a_set);
	}


	/**
	 * @param $a_set
	 */
	protected function setCtrlParametersForRow($a_set) {
		$this->ctrl->setParameterByClass(get_class($this->parent_obj), 'ar_id', self::domid_encode($a_set[$this->getFields()->getPrimaryField()
			->getName()]));
	}


	/**
	 * @param $a_set
	 */
	protected function addMultiItemActionCheckboxToRow($a_set) {
		if ($this->getMultiItemActions()) {
			$this->tpl->setVariable('ID', self::domid_encode($a_set[$this->getFields()->getPrimaryField()->getName()]));
		}
	}


	/**
	 * @param $a_set
	 */
	protected function parseRow($a_set) {
		foreach ($a_set as $key => $value) {
			$field = $this->getField($key);
			if ($this->checkColumnVisibile($field)) {
				$this->parseEntry($field, $value);
			}
		}
	}


	/**
	 * @param arIndexTableField $field
	 * @param mixed             $value
	 */
	protected function parseEntry(arIndexTableField $field, $value) {
		$this->tpl->setCurrentBlock('entry');
		$this->tpl->setVariable('ENTRY_CONTENT', $value);
		$this->tpl->parseCurrentBlock('entry');
	}


	/**
	 * @param $a_set
	 */
	protected function addActionsToRow($a_set) {
		if ($this->getActions()) {
			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId(self::domid_encode($a_set[$this->getFields()->getPrimaryField()->getName()]));
			$alist->setListTitle($this->txt('actions', false));

			foreach ($this->getActionsAsArray() as $action) {
				/**
				 * @var arIndexTableAction $action
				 */
				$access = true;
				if ($action->getAccess()) {
					$access = $this->access->checkAccess($action->getAccess(), '', $_GET['ref_id']);
				}
				if ($access) {
					$alist->addItem($action->getTitle(), $action->getId(), $this->ctrl->getLinkTargetByClass($action->getTargetClass(), $action->getTargetCmd()));
				}
			}

			$this->tpl->setVariable('ACTION', $alist->getHTML());
		}
	}


	/**
	 * @return string
	 */
	public function render() {

		$index_table_tpl = new ilTemplate("tpl.index_table.html", true, true, "./Customizing/global/plugins/Libraries/ActiveRecord/");
		if ($this->getToolbar()) {
			$index_table_tpl->setVariable("TOOLBAR", $this->getToolbar()->getHTML());
		}

		$index_table_tpl->setVariable("TABLE", parent::render());

		return $index_table_tpl->get();
	}


	/**
	 * @param      $txt
	 * @param bool $plugin_txt
	 *
	 * @return string
	 */
	public function txt($txt, $plugin_txt = true) {
		return $this->parent_obj->txt($txt, $plugin_txt);
	}


	public function applyFilter() {
		$this->writeFilterToSession();
		$this->resetOffset();
		$this->initTableData();
	}


	public function resetFilter() {
		parent::resetFilter();
		$this->resetOffset();
		$this->initTableData();
	}


	/**
	 * @param $id_to_encode
	 *
	 * @return mixed|null
	 * @description  Encode a string for use as a DOM id.
	 * Replaces non-alphanumeric characters with an underscore and the hex representation of the character code with letters in lowercase see: http://brightonart.co.uk/blogs/dom-id-encode-php-function
	 */
	public static function domid_encode($id_to_encode) {
		$encoded_id = NULL;
		if (!empty($id_to_encode)) {
			$encoded_id = preg_replace_callback('/([^a-zA-Z0-9])/', function ($matches) {
				return "__idstart_" . strtolower(dechex(ord($matches[0]))) . "_idend__";
			}, $id_to_encode);
		}

		return $encoded_id;
	}


	/**
	 * @param $id_to_decode
	 *
	 * @return mixed
	 * @description  Decode a DOM id encoded by domid_encode().
	 */
	public static function domid_decode($id_to_decode) {
		$decoded_id = "";
		if (!empty($id_to_decode)) {
			$decoded_id = preg_replace_callback('/__idstart_(.{2})_idend__/', function ($matches) {
				return chr(hexdec($matches[1]));
			}, $id_to_decode);
		}

		return $decoded_id;
	}


	/**
	 * @param arIndexTableFields $fields
	 */
	function setFields(arIndexTableFields $fields) {
		$this->fields = $fields;
	}


	/**
	 * @return arIndexTableFields
	 */
	public function getFields() {
		return $this->fields;
	}


	/**
	 * @return arIndexTableField[]
	 */
	public function getFieldsAsArray() {
		return $this->getFields()->getFields();
	}


	/**
	 * @param $field_name
	 *
	 * @return arIndexTableField
	 */
	public function getField($field_name) {
		return $this->getFields()->getField($field_name);
	}


	/**
	 * @param arIndexTableField
	 */
	public function addField(arIndexTableField $field) {
		$this->getFields()->addField($field);
	}


	/**
	 * @param string $table_title
	 */
	public function setTableTitle($table_title) {
		$this->table_title = $table_title;
	}


	/**
	 * @return string
	 */
	public function getTableTitle() {
		return $this->table_title;
	}


	/**
	 * @param \ilToolbarGUI $toolbar
	 */
	public function setToolbar($toolbar) {
		$this->toolbar = $toolbar;
	}


	/**
	 * @return \ilToolbarGUI
	 */
	public function getToolbar() {
		return $this->toolbar;
	}


	/**
	 * @param \arIndexTableActions $actions
	 */
	public function setActions($actions) {
		$this->actions = $actions;
	}


	/**
	 * @return \arIndexTableActions
	 */
	public function getActions() {
		return $this->actions;
	}


	/**
	 * @return \arIndexTableAction[]
	 */
	public function getActionsAsArray() {
		return $this->actions->getActions();
	}


	/**
	 * @param \arIndexTableActions $multi_item_actions
	 */
	public function setMultiItemActions($multi_item_actions) {
		$this->multi_item_actions = $multi_item_actions;
	}


	/**
	 * @return \arIndexTableActions
	 */
	public function getMultiItemActions() {
		return $this->multi_item_actions;
	}


	/**
	 * @return \arIndexTableActions
	 */
	public function getMultiItemActionsAsArray() {
		return $this->multi_item_actions;
	}
}