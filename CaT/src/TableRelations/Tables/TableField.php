<?php

namespace CaT\TableRelations\Tables;

use CaT\Filter as Filters;
/**
 * @inheritdoc
 */
class TableField extends Filters\Predicates\Field implements AbstractTableField {

	public function __construct(Filters\PredicateFactory $f, $name, $table_id = null) {
		$this->table_id = $table_id;
		parent::__construct($f, $name);
	}

	/**
	 * @inheritdoc
	 */
	public function tableId() {
		return $this->table_id;
	}

	/**
	 * @inheritdoc
	 */
	public function name() {
		return $this->table_id.'.'.parent::name();
	}

	/**
	 * @inheritdoc
	 */
	public function name_simple() {
		return parent::name();
	}

	public function setTableId($table_id) {
		$this->table_id = $table_id;
	}
}
