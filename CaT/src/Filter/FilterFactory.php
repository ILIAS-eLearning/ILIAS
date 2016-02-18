<?php

/* Copyright (c) 2015 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter;

/**
 * Factory to build filters.
 *
 * A filter is a way to build a predicate from some inputs.
 */
class FilterFactory {
	/**
	 * @var PredicateFactory
	 */
	protected $predicate_factory;

	public function __construct(PredicateFactory $predicate_factory) {
		$this->predicate_factory = $predicate_factory;
	}

	public function predicate_factory() {
		return $this->predicate_factory;
	}

	/**
	 * Get a filter over a period.
	 *
	 * @param	string	$label
	 * @param	string	$description
	 * @return	Filters\DatePeriodFilter
	 */
	public function dateperiod($label, $description) {
		return new Filters\DatePeriodFilter($this, $label, $description);
	}

	/**
	 * Get a filter that represents an option.
	 *
	 * @param	string	$label
	 * @param	string	$description
	 * @return	Filters\Filter
	 */
	public function option($label, $description) {
		
	}

	/**
	 * Get a filter that represents a choice of some options from a list
	 * of possible options.
	 *
	 * @param	string	$label
	 * @param	string	$description
	 * @param	array	$options		int|string => string
	 * @return	Filters\Filter
	 */
	public function multiselect($label, $description, $options) {
		
	}

	/**
	 * Get a filter that uses some text for filtering.
	 *
	 * @param	string	$label
	 * @param	string	$description
	 * @return	Filters\Filter
	 */
	public function text($label, $description) {
		
	}

	/**
	 * Map this over a dateperiod to get the standard behaviour of
	 * overlapping periods.
	 *
	 * @param	string	$field_start
	 * @param	string	$field_end
	 * @return	\Closure
	 */
	public function dateperiod_overlaps_predicate($field_start, $field_end) {
		$f = $this->predicate_factory();
		
		return function(\DateTime $start, \DateTime $end) 
				use ($field_start, $field_end, $f) {
			return	$f->field($field_start)->LT()->date($end)
				->_AND()->
					$f->field($field_end)->GT()->date($start);
		};
	}
}