<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Predicates;

/**
 * A predicate to compare two values.
 */
abstract class PredicateComparison extends Predicate {
	/**
	 * @var ilValueLike
	 */
	protected $l;
	
	/**
	 * @var ilValueLike
	 */
	protected $r;

	public function __construct(\ILIAS\TMS\Filter\PredicateFactory $factory, ValueLike $l, ValueLike $r) {
		$this->l = $l;
		$this->r = $r;
		$this->setFactory($factory);
	}

	public function fields() {
		return $this->addPossibleFieldsToFields(array($this->l, $this->r),array());
	}

	/**
	 * Get the left side of the comparison.
	 *
	 * @return	ilValueLike
	 */
	public function left() {
		return $this->l;
	}

	/**
	 * Get the right side of the comparison.
	 *
	 * @return	ilValueLike
	 */
	public function right() {
		return $this->r;
	}
}
