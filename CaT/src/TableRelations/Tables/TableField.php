<?php

namespace \CaT\TableRelations\Tables;

use \CaT\Filter\Predicates as Predicates;

class TableField extends Predicates\Field implements abstractTableField {

	public function __construct(Predicates\PredicateFactory $f, $name, $table_id = null) {
		$this->table_id = $table_id;
		parent::__construct($f, $name);
	}

	public function tableId() {
		return $this->table_id;
	}
}