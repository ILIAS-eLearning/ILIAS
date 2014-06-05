<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.srModelObjectTableGUI.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/class.ActiveRecordList.php');

/**
 * GUI-Class arIndexTableGUI
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.4
 *
 */
class arIndexTableGUI extends srModelObjectTableGUI {

	/**
	 * @var array
	 */
	protected $fields_to_hide = array();
	/**
	 * @var ActiveRecordList
	 */
	protected $active_record_list = NULL;
	/**
	 * @var array
	 */
	protected $actions = array();
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar = NULL;
	/**
	 * @var arGUI|null
	 */
	protected $parent_gui = NULL;
	/**
	 * @var string
	 */
	protected $table_title = '';


	/**
	 * @var array $data = null;
	 */
	public function __construct(arGUI $a_parent_obj, $a_parent_cmd, ActiveRecordList $active_record_list) {
		$this->active_record_list = $active_record_list;
		$this->parent_gui = $a_parent_obj;
		$title = strtolower(str_replace("Record", "", get_class($this->active_record_list->getAR()))) . "_index";
		$this->setTableTitle($this->txt($title));
		$this->initFieldsToHide();
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->initToolbar();
		$this->addActions();
	}


	protected function addActions() {
		global $lng;

		$this->addAction('view', $lng->txt('view'), get_class($this->parent_obj), 'view', 'view');
		$this->addAction('edit', $lng->txt('edit'), get_class($this->parent_obj), 'edit', 'write');
		$this->addAction('delete', $lng->txt('delete'), get_class($this->parent_obj), 'delete', 'write');
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
	 * @param array $fields_to_hide
	 */
	public function setFieldsToHide($fields_to_hide) {
		$this->fields_to_hide = $fields_to_hide;
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
	 * @return array
	 */
	public function getFieldsToHide() {
		return $this->fields_to_hide;
	}


	protected function initFieldsToHide() {
	}


	protected function initToolbar() {
		$toolbar = new ilToolbarGUI();
		$toolbar->addButton($this->txt("add_message"), $this->ctrl->getLinkTarget($this->parent_obj, "add"));
		$this->setToolbar($toolbar);
	}


	protected function initTableData() {
		$this->setData($this->active_record_list->getArray());
	}


	/**
	 * @return bool
	 * @description returns false, if no filter is needed, otherwise implement filters
	 *
	 */
	protected function initTableFilter() {
		return false;
	}


	/**
	 * @return bool
	 * @description returns false or set the following
	 * @description e.g. override table id oder title: $this->table_id = 'myid', $this->table_title = 'My Title'
	 */
	protected function initTableProperties() {
		return false;
	}


	/**
	 * @return bool
	 * @description return false or implements own form action and
	 */
	//TODO GET ersetzen
	protected function initFormActionsAndCmdButtons() {
		return false;
	}


	/**
	 * @description implement your fillRow
	 *
	 * @param $a_set
	 *
	 * @return bool
	 */
	protected function fillTableRow($a_set) {
		$this->setCtrlParametersForRow($a_set);
		$this->addRow($a_set);
		$this->addActionsToRow($a_set);
	}


	protected function setCtrlParametersForRow($a_set) {
		$this->ctrl->setParameterByClass(get_class($this->parent_obj), 'ar_id', ($a_set['id']));
	}


	protected function addRow($a_set) {
		$this->tpl->setVariable('ID', $a_set['id']);

		foreach ($a_set as $key => $value) {
			if (! in_array($key, $this->fields_to_hide)) {
				$field = $this->active_record_list->getAR()->getArFieldList()->getFieldByName($key);
				$this->addFieldToRow($field, $value);
			}
		}
	}


	protected function addFieldToRow($field, $value) {
		$this->tpl->setCurrentBlock('entry');
		if ($value == NULL) {
			$this->setEmptyFields($field, $value);
		} else {
			switch ($field->getFieldType()) {
				case 'integer':
				case 'float':
					$this->setNumericData($field, $value);
					break;
				case 'text':
					$this->setTextData($field, $value);
					break;
				case 'clob':
					$this->setClobData($field, $value);
					break;
				case 'date':
				case 'time':
				case 'timestamp':
					$this->setDateTimeData($field, $value);
					break;
			}
		}
		$this->beforeParseCurrentRowBlock($field, $value);
		$this->tpl->parseCurrentBlock();
	}


	protected function beforeParseCurrentRowBlock(arField $field, $value) {
	}


	protected function setNumericData(arField $field, $value) {
		$this->tpl->setVariable('ENTRY_CONTENT', $value);
	}


	protected function setTextData(arField $field, $value) {
		$this->tpl->setVariable('ENTRY_CONTENT', $value);
	}


	protected function setDateTimeData(arField $field, $value) {
		$datetime = new ilDateTime($value, IL_CAL_DATETIME);
		$this->tpl->setVariable('ENTRY_CONTENT', ilDatePresentation::formatDate($datetime, IL_CAL_UNIX));
	}


	protected function setClobData(arField $field, $value) {
		$this->tpl->setVariable('ENTRY_CONTENT', $value);
	}


	public function setEmptyFields($field) {
		$this->tpl->setVariable('ENTRY_CONTENT', " ");
	}


	public function addAction($id, $title, $target_class, $target_cmd, $access) {
		$this->actions[$id] = new stdClass();
		$this->actions[$id]->id = $id;
		$this->actions[$id]->title = $title;
		$this->actions[$id]->target_class = $target_class;
		$this->actions[$id]->target_cmd = $target_cmd;
		$this->actions[$id]->access = $access;
	}


	protected function addActionsToRow($a_set) {
		if (! empty($this->actions)) {
			$alist = new ilAdvancedSelectionListGUI();
			$alist->setId($a_set['id']);
			$alist->setListTitle($this->txt('actions', false));

			foreach ($this->actions as $action) {
				if (($this->access->checkAccess($action->access, '', $_GET['ref_id']))) {
					$alist->addItem($action->title, $action->id, $this->ctrl->getLinkTargetByClass($action->target_class, $action->target_cmd));
				}
			}

			$this->tpl->setVariable('ACTION', $alist->getHTML());
		}
	}


	/**
	 * @return bool
	 * @description returns false, if automatic columns are needed, otherwise implement your columns
	 */
	protected function initTableColumns() {
		$this->addColumn('', '', '1', true);

		if ($this->getData()) {
			foreach (array_pop($this->getData()) as $key => $item) {
				if (! in_array($key, $this->fields_to_hide)) {
					$this->addColumn($this->txt($key));
				}
			}
			$this->addColumn('actions');
		}
	}


	/**
	 * @return bool
	 * @description returns false if standard-table-header is needes, otherwise implement your header
	 */
	protected function initTableHeader() {
		return false;
	}


	/**
	 * @return bool
	 * @description returns false, if dynamic template is needed, otherwise implement your own template by $this->setRowTemplate($a_template, $a_template_dir = '')
	 */
	protected function initTableRowTemplate() {
		$this->setRowTemplate('tpl.record_row.html', './Customizing/global/plugins/Libraries/ActiveRecord/');
	}


	/**
	 * @return bool
	 * @description returns false, if global language is needed; implement your own language by setting $this->pl
	 */
	protected function initLanguage() {
		return false;
	}


	public function render() {

		$index_table_tpl = new ilTemplate("tpl.index_table.html", true, true, "./Customizing/global/plugins/Libraries/ActiveRecord/");
		if ($this->getToolbar()) {
			$index_table_tpl->setVariable("TOOLBAR", $this->getToolbar()->getHTML());
		}

		$index_table_tpl->setVariable("TABLE", parent::render());

		return $index_table_tpl->get();
	}


	protected function txt($txt, $plugin_txt = true) {
		return $this->parent_gui->txt($txt, $plugin_txt);
	}
}

?>