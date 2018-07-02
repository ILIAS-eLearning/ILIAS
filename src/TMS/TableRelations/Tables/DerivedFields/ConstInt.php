<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

/**
 * Calculate the average over a field.
 */
class ConstInt extends T\DerivedField  {

	protected $value;

	public function __construct(Filters\PredicateFactory $f, $name, $value) {
		assert('is_int($value)');
		$this->value = $value;
		parent::__construct($f, $name);
	}

	/**
	 * Get the value this field represents.
	 *
	 * @return	int
	 */
	public function value()
	{
		return $this->value;
	}
}
