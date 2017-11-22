<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

/**
 * Calculate the average over a field.
 */
class Avg extends T\DerivedField  {
	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $field) {
		$this->derived_from[] = $field;
		$this->arg = $field;
		parent::__construct($f, $name);
	}

	/**
	 * The averaged field.
	 *
	 * @return AbstractField
	 */
	public function argument() {
		return $this->arg;
	}
}
