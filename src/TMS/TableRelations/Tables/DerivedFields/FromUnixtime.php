<?php

namespace ILIAS\TMS\TableRelations\Tables\DerivedFields;
use ILIAS\TMS\TableRelations\Tables as T;
use ILIAS\TMS\Filter as Filters;
/**
 * Translate a timestamp into ISO DateTime
 */
class FromUnixtime extends T\DerivedField  {
	public function __construct(Filters\PredicateFactory $f, $name, Filters\Predicates\Field $field) {
		$this->derived_from[] = $field;
		$this->arg = $field;
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
}
