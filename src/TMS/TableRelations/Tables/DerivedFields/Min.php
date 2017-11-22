<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

/**
 * Determine the minimum entry in a field.
 */
class Min extends T\DerivedField {
	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $field) {
		$this->derived_from[] = $field;
		$this->arg = $field;
		parent::__construct($f, $name);
	}

	/**
	 * The field from which the min is being determined.
	 *
	 * @return AbstractField
	 */
	public function argument() {
		return $this->arg;
	}
}
