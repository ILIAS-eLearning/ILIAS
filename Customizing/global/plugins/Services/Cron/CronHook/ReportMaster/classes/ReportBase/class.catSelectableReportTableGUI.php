<?php
/**
 * Table with selectable columns for report taking care of requested fields
 */
class catSelectableReportTableGUI extends catTableGUI {
	protected $persistent = array();
	protected $order = array();
	public function __construct($a_parent_gui, $a_cmd, $a_tpl_context, $external_sorting = true) {
		global $ilCtrl;

		parent::__construct($a_parent_gui, $a_cmd, $a_tpl_context);
		$this->setEnableTitle(false);
		$this->setTopCommands(false);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_gui,$a_cmd));
		$this->columns_determined = false;
		$this->setId("report_table");
		$this->external_sorting = $external_sorting;
	}

	/**
	 * Define a column depending on one or several fields. I.e. fields are requested if column is activated.
	 *
	 * @param	string	$title 
	 * @param	string	$column_id
	 * @param	AbstractField[field_id]	$fields 
	 * @param	bool	$selectable 
	 * @param 	bool	$sort 
	 * @param 	bool	$no_excel
	 */
	public function defineFieldColumn($title, $column_id, array $fields = array(), $selectable = false, $sort = true , $no_excel =  false) {
		$this->fields[$column_id] = $fields;
		$this->order[] = $column_id;
		if($selectable) {
			$this->selectable[$column_id] = array('txt' => $title);
			if($sort) {
				$this->selectable[$column_id]['sort'] = $column_id;
			}
			$this->selectable[$column_id]['no_excel'] = $no_excel;
		} else {
			$this->persistent[$column_id] = array('txt' => $title);
			if($sort) {
				$this->persistent[$column_id]['sort'] = $column_id;
			}
			$this->persistent[$column_id]['no_excel'] = $no_excel;
		}
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getSelectableColumns() {
		return $this->selectable;
	}

	/**
	 * Selectable-selected and persistent column info
	 *
	 * @return mixed[][]
	 */
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

	/**
	 * Fields associated with relevant columns
	 *
	 * @return Field[]
	 */
	protected function relevantFields() {
		$return = array();
		foreach ($this->relevantColumns() as $column_id => $vals) {
			$return = array_merge($return, $this->fields[$column_id]);
		}
		return $return;
	}

	/**
	 * Field ids associated with relevant columns
	 *
	 * @return string[]
	 */
	protected function relevantFieldIds() {
		return array_keys($this->relevantFields());
	}

	/**
	 * @inheritdoc
	 */
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

	/**
	 * According to selection addColumns
	 */
	protected function spanColumns() {
		$this->addColumn("", "blank", "0px", false);
		$relevant = $this->relevantColumns();
		foreach ($this->order as $column_id) {
			if(isset($relevant[$column_id])) {
				if(isset($relevant[$column_id]['sort'])) {
					$this->addColumn($relevant[$column_id]['txt'],$relevant[$column_id]['sort']);
				} else {
					$this->addColumn($relevant[$column_id]['txt']);
				}
			}
		}
	}

	/**
	 * According to selection request fields from space
	 */
	public function prepareTableAndSetRelevantFields($space) {
		$this->determineSelectedColumns();
		$this->spanColumns();
		$this->setExternalSorting($this->external_sorting);
		foreach($this->relevantFields() as $id => $field) {
			$space->request($field,$id);
		}
		if($this->external_sorting) {
			$this->determineOffsetAndOrder(true);
			$order_column_id = $this->getOrderField();
			if(isset($this->relevantColumns()[$order_column_id])) {
				$order_direction = $this->getOrderDirection();
				$space->orderBy(array_keys($this->fields[$order_column_id]),$order_direction);
			} else {
				$space->orderBy(array(key($this->relevantColumns())),'asc');
			}
		}
		return $space;
	}
}