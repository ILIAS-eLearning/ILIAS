<?php

class catSelectableReportTableGUI extends catTableGUI {
	protected $persistent = array();
	protected $order = array();
	public function __construct($a_parent_gui, $a_cmd, $a_tpl_context) {
		global $ilCtrl;

		parent::__construct($a_parent_gui, $a_cmd, $a_tpl_context);
		$this->setEnableTitle(false);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_gui,$a_cmd));

		$this->columns_determined = false;
		$this->setId("report_table");
	}

	public function defineFieldColumn($title, $column_id, array $fields = array(), $selectable = false, $sort = true ) {
		$this->fields[$column_id] = $fields;
		$this->order[] = $column_id;
		if($selectable) {
			$this->selectable[$column_id] = array('txt' => $title, 'sort' => $sort);
		} else {
			$this->persistent[$column_id] = array('txt' => $title, 'sort' => $sort);
		}
		return $this;
	}

	public function getSelectableColumns() {
		return $this->selectable;
	}

	public function relevantColumns() {
		$relevant_column_info = array();
		$relevant_column_info_pre = array();
		foreach ($this->persistent as $column_id => $vals) {
			$relevant_column_info_pre[$column_id] = $vals;
		}
		foreach ($this->getSelectedColumns() as $column_id => $vals) {
			$relevant_column_info_pre[$column_id] = $this->selectable[$column_id];
		}
		foreach ($this->order as $column_id) {
			if(isset($relevant_column_info_pre[$column_id])) {
				$relevant_column_info[$column_id] = $relevant_column_info_pre[$column_id];
			}
		}
		return $relevant_column_info;
	}

	protected function relevantFields() {
		$return = array();
		foreach ($this->relevantColumns() as $column_id => $vals) {
			$return = array_merge($return, $this->fields[$column_id]);
		}
		return $return;
	}

	protected function relevantFieldIds() {
		return array_keys($this->relevantFields());
	}

	public function fillRow($set) {
		$relevant = $this->relevantColumns();
		foreach ($this->order as $column_id) {
			if(isset($relevant[$column_id])) {
				$this->tpl->setCurrentBlock($column_id);
				$this->tpl->setVariable('VAL_'.strtoupper($column_id),$set[$column_id]);
				$this->tpl->parseCurrentBlock();
			}
		}
	}

	protected function spanColumns() {
		$relevant = $this->relevantColumns();
		foreach ($this->order as $column_id) {
			if(isset($relevant[$column_id])) {
				$this->addColumn($relevant[$column_id]['txt'],$relevant[$column_id]['sort']);
			}
		}
	}

	public function prepareTableAndSetRelevantFields($space) {
		$this->determineSelectedColumns();
		$this->spanColumns();
		foreach($this->relevantFields() as $id => $field) {
			$space->request($field,$id);
		}
		return $space;
	}
}