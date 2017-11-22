<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

/**
 * Calculate the sum over all entries of a field.
 */
class Sum extends T\DerivedField {
	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $field) {
		$this->derived_from[] = $field;
		$this->arg = $field;
		parent::__construct($f, $name);
	}

	/**
	 * The field over which the sum is formed.
	 *
	 * @return AbstractField
	 */
	public function argument() {
		return $this->arg;
	}
}
