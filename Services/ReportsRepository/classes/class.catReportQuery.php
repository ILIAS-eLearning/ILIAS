<?php 

class catReportQuery {
	protected function __construct() {
		$this->fields = array();
		$this->_select_raw = array();
		$this->_from = null;
		$this->joins = array();
		$this->left_joins = array();
		$this->raw_joins = array();
		$this->nonfilter_where = array();
		$this->compiled = false;
		$this->sql_str = null;
		$this->sql_from = null;
		$this->_distinct = false;
		$this->_group_by = array();
	}

	public static function create() {
		return new catReportQuery();
	}

	public function distinct() {
		$this->_distinct = true;
		return $this;
	}
    
	public function where($sttmnt) {
		$this->checkNotCompiled();
		$this->nonfilter_where[] = $sttmnt;
		return $this;
	}

	public function getSqlWhere() {
		if(count($this->nonfilter_where) > 0) {
			return implode(' AND ',$this->nonfilter_where);
		}
		return 'TRUE';
	}

	public function select($a_field) {
		$this->checkNotCompiled();
		if (!is_array($a_field)) {
			$this->fields[] = $a_field;
		}
		else {
			$this->fields = array_merge($this->fields, $a_fields);
		}
		return $this;
	}

	public function select_raw($a_stmt) {
		$this->_select_raw[] = $a_stmt;
		return $this;
	}
    
	public function from($a_table) {
		$this->checkNotCompiled();
		if ($this->_from !== null) {
			throw new Exception("catReportQuery::from: already defined.");
		}

		$this->_from = $a_table;
		return $this;
	}

	public function join($a_table) {
		$this->checkNotCompiled();
		return new catReportQueryOn($this, $this->joins, $a_table);
	}

	public function left_join($a_table) {
		$this->checkNotCompiled();
		return new catReportQueryOn($this, $this->left_joins, $a_table);
	}

	public function raw_join($sql) {
		$this->raw_joins[] = $sql;
		return $this;
	}

	public function group_by($a_column) {
		$this->_group_by[] = $a_column;
		return $this;
	}

	public function sql() {
		if( $this->sql_str !== null) {
			return $this->sql_str;
		}
		$this->checkCompiled("sql");

		$escp = array();
		foreach ($this->fields as $field) {
			$escp[] = catFilter::quoteDBId($field);
		}

		$this->sql_str = 
			"SELECT "
			.($this->_distinct ? "DISTINCT " : "")
			.implode("\n\t,", $escp)
			.(count($this->_select_raw) ? "\n\t," : "")
			.implode("\n\t,", $this->_select_raw)
			.$this->sqlFrom()
			;

		return $this->sql_str;
	}
    
	public function sqlFrom() {

		if ($this->sql_from === null) {
			$this->sql_from =
			"\n FROM ".$this->_from[0]." ".$this->_from[1]."\n "
			// TODO: this might break the query since it does not respect
			// the order in which the user defined the
			.implode("\n ", $this->joins)."\n "
			.implode("\n ", $this->left_joins)."\n"
			.implode("\n ", $this->raw_joins)."\n";
		}

		return $this->sql_from;
	}

	public function sqlGroupBy() {
		if (!count($this->_group_by)) {
		return "";
		}
    
		$cols = array();
		foreach ($this->_group_by as $col) {
			$cols[] = catFilter::quoteDBId($col);
		}

		return " GROUP BY ".implode(", ", $cols);
	}

	public function compile() {
		$this->checkNotCompiled();

		if (count($this->fields) === 0 && count($this->_select_raw) === 0) {
			throw new Exception("catReportQuery::compile: No fields defined.");
		}
		if ($this->_from === null) {
			throw new Exception("catReportQuery::compile: No FROM-table defined.");
		}

		$this->_from = $this->rectifyTableName("from", $this->_from);
		foreach($this->joins as $key => $value) {
			$tab = $this->rectifyTableName("join", $value[0]);
			$this->joins[$key] = " JOIN ".$tab[0]." ".$tab[1]." ON ".$value[1]." ";
		}
		foreach($this->left_joins as $key => $value) {
			$tab = $this->rectifyTableName("left join", $value[0]);
			$this->left_joins[$key] = " LEFT JOIN ".$tab[0]." ".$tab[1]." ON ".$value[1]." ";
		}

		$this->compiled = true;

		return $this;
	}

	protected function checkNotCompiled() {
		if ($this->compiled) {
			throw new Exception("catReportQuery::checkCompiled: Don't modify a filter you already compiled.");
		}
	}

	protected function checkCompiled($a_what) {
		if (!$this->compiled) {
			throw new Exception("catReportQuery::checkCompiled: Don't ".$a_what." a filter you did not compile.");
		}
	}
    
	protected function rectifyTableName($a_what, $name) {
		$spl = explode(" ", $name);
		if (count($spl) > 2) {
			throw new Exception("catReportQuery::rectifiyTableName: Expected ".$a_what." to contain one space at most.");
		}
		if (count($spl) == 1) {
			$spl[] = "";
		}

		$spl[0] = catFilter::quoteDBId($spl[0]);
		return $spl;
	}
}
