<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

/**
 * Calculate the average over a field.
 */
class Concat extends T\DerivedField  {
	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $field_1, Filters\Predicates\Field $field_2, $inbetween = null) {
		$this->derived_from[] = $field_1;
		$this->derived_from[] = $field_2;
		$this->field_1 = $field_1;
		$this->field_2 = $field_2;
		$this->inbetween = $inbetween;
		parent::__construct($f, $name);
	}

	/**
	 * The concat field 1.
	 *
	 * @return AbstractField
	 */
	public function fieldOne() {
		return $this->field_1;
	}

	/**
	 * The concat field 2.
	 *
	 * @return AbstractField
	 */
	public function fieldTwo() {
		return $this->field_2;
	}

	public function inbetween()
	{
		return $this->inbetween;
	}
}
