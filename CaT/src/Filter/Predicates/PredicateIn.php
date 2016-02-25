<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Predicates;

/**
 * A predicate to check Value-field contains value.
 */

class PredicateIn extends Predicate{
	protected $value;
	protected $list;
	protected $factory;

	public function __construct(\CaT\Filter\PredicateFactory $factory, ValueLike $value, ValueList $list) {
		$this->setFactory($factory);
		$this->value = $value;
		$this->list = $list;
	}

	public function getValue() {
		return $this->value;
	}

	public function getList() {
		return $this->list;
	}

	public function fields() {
		$fields = $this->addPossibleFieldsToFields($this->list, array());
		return $this->addPossibleFieldsToFields($this->value, $fields);
	}
}