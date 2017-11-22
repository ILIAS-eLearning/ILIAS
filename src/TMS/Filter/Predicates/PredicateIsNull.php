<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Predicates;

/**
 * A predicate to check whether some valueLike instance is null
 */

class PredicateIsNull extends Predicate {
	protected $value;
	protected $factory;

	public function __construct(\ILIAS\TMS\Filter\PredicateFactory $factory, ValueLike $value) {
		$this->setFactory($factory);
		$this->value = $value;
	}

	public function fields() {
		return $this->addPossibleFieldsToFields(array($this->value),array());
	}
}
