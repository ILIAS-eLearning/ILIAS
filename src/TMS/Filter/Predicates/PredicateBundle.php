<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace ILIAS\TMS\Filter\Predicates;

/**
 * A bundle of some predicates.
 */
abstract class PredicateBundle extends Predicate {
	/**
	 * @var	Predicate[]
	 */
	protected $subs = array();

	public function __construct( \ILIAS\TMS\Filter\PredicateFactory $factory, array $subs) {
		$this->subs = array_map(function(Predicate $p) {
			return $p;
		}, $subs);

		$this->setFactory($factory);
	}


	public function fields() {
		$fields = array();
		foreach ($this->subs as $sub) {
			$fields = $this->addPossibleFieldsToFields($sub->fields(), $fields);
		}
		return $fields;
	}

	/**
	 * Get the bundled predicates a array.
	 *
	 * @return	ilPredicate[]
	 */
	public function subs() {
		return array_merge($this->subs, array());
	}
}
