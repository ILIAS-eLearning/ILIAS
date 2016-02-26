<?php

class catReportTable {
	protected function __construct() {
		$this->columns = array();
		$this->all_columns = array();
		$this->row_template_filename = null;
		$this->row_template_module = null;
		$this->_group_by = null;
		$this->group_head_template_filename = null;
		$this->group_head_template_module = null;
	}

	public static function create() {
		return new catReportTable();
	}
    
	public function column($a_id, $a_title, $a_no_lng_var = false, $a_width = "", $a_no_excel = false, $a_sorting = true) {
		$this->columns[$a_id] = array( $a_id
			, $a_title
			, $a_no_lng_var
			, $a_width
			, $a_no_excel
			, $a_sorting
			);
		$this->all_columns[$a_id] = $this->columns[$a_id];
		return $this;
	}

	public function template($a_filename, $a_module) {
		$this->row_template_filename = $a_filename;
		$this->row_template_module = $a_module;
		return $this;
	}

	public function group_by($a_cols, $a_filename, $a_modules) {
		if ($this->_group_by !== null) {
			throw new Exception("catReportTable::group_by: Grouping already defined.");
		}
	
		if (!is_array($a_cols) || count($a_cols) == 0) {
			throw new Exception("catReportTable::group_by: Expected first argument to be an array "
				."with at least one entry.");
		}

		$this->_group_by = array();

		foreach ($a_cols as $col_name) {
			if (!array_key_exists($col_name, $this->columns)) {
				throw new Exception("catReportTable::group_by: Can't group by unknown column ".$col_name);
			}

			$this->_group_by[$col_name] = $this->columns[$col_name];
			unset($this->columns[$col_name]);
		}

		$this->group_head_template_filename = $a_filename;
		$this->group_head_template_module = $a_modules;

		return $this;
	}
}