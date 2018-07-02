<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;

class DateFormat extends T\DerivedField  {
	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $field, $format = '%d.%m.%Y') {
		assert('is_string($format)');
		$this->derived_from[] = $field;
		$this->arg = $field;
		$this->format = $format;
		parent::__construct($f, $name);
	}

	/**
	 * The transformed field field.
	 *
	 * @return AbstractField
	 */
	public function argument() {
		return $this->arg;
	}

	public function format() {
		return $this->format;
	}

}