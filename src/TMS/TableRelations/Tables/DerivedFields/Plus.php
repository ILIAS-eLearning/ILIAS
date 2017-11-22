<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

/**
 * Form the sum of two fieldentries in the same row.
 */
class Plus extends T\DerivedField {
	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $left, Filters\Predicates\Field $right) {
		$this->derived_from[] = $left;
		$this->derived_from[] = $right;
		$this->left = $left;
		$this->right = $right;
		parent::__construct($f, $name);
	}

	/**
	 * First summand field.
	 * @return AbstractField
	 */
	public function left() {
		return $this->left;
	}

	/**
	 * Secont summand field.
	 * @return AbstractField
	 */
	public function right() {
		return $this->right;
	}
}
