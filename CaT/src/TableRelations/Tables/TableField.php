<?php

namespace \CaT\TableRelations\Tables;

use \CaT\Filter\Predicates as Predicates;
/**
 * A field combining Predicate-Fields and table functinality.
 * Note: it may still be used in Predicates.
 */
class TableField extends Predicates\Field implements abstractTableField {

	public function __construct(Predicates\PredicateFactory $f, $name, $table_id = null) {
		$this->table_id = $table_id;
		parent::__construct($f, $name);
	}

	/**
	 * Any TableField may be related to a Table.
	 * Two different Tables may contain fields with equal name.
	 */
	public function tableId() {
		return $this->table_id;
	}
}