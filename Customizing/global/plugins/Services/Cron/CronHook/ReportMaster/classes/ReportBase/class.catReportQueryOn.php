<?php

class catReportQueryOn {
	public function __construct($a_query, &$a_joins, $a_table) {
		$this->query = $a_query;
		$this->joins = &$a_joins;
		$this->table = $a_table;
	}

	public function on($a_condition) {
		$this->joins[] = array($this->table, $a_condition);
		return $this->query;
	}
}
