<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

/**
 * Group concat all the entries in a field.
 */
class IfThenElse extends T\DerivedField  {

	protected $condition;
	protected $then;
	protected $else;

	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Predicate $condition, Filters\Predicates\Field $then, Filters\Predicates\Field $else) {
		$this->derived_from = $condition->fields();
		$this->derived_from[] = $then;
		$this->derived_from[] = $else;
		$this->condition = $condition;
		$this->then = $then;
		$this->else = $else;
		parent::__construct($f, $name);
	}

	/**
	 * Field-defining condition.
	 *
	 * @return Predicate
	 */
	public function condition() {
		return $this->condition;
	}

	/**
	 * Derived field reprsenting fulfilled condition.
	 *
	 * @return 	Field
	 */
	public function met() {
		return $this->then;
	}

	/**
	 * Derived field reprsenting unfulfilled condition.
	 *
	 * @return 	Field
	 */
	public function notMet() {
		return $this->else;
	}
}
