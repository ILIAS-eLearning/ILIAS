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
	 * Get a filter over a period.
	 *
	 * @param	string	$label
	 * @param	string	$description
	 * @return	Filters\Filter
	 */
	public function dateperiod($label, $description) {
		
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
	 * Get a filter that represents an option.
	 *
	 * @param	string	$label
	 * @param	string	$description
	 * @param	array	$options		int|string => string
	 * @return	Filters\Filter
	 */
	public function multiselect($label, $description, $options) {
		
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
		
	}
}