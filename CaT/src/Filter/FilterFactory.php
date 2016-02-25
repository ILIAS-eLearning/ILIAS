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

	/**
	 * @var TypeFactory
	 */
	protected $type_factory;

	public function __construct(PredicateFactory $predicate_factory, TypeFactory $type_factory) {
		$this->predicate_factory = $predicate_factory;
		$this->type_factory = $type_factory;
	}

	public function predicate_factory() {
		return $this->predicate_factory;
	}

	public function type_factory() {
		return $this->type_factory;
	}

	/**
	 * Get a filter over a period.
	 *
	 * @param	string	$label
	 * @param	string	$description
	 * @return	Filters\DatePeriodFilter
	 */
	public function dateperiod($label, $description) {
		return new Filters\DatePeriod($this, $label, $description);
	}

	/**
	 * Get a filter that represents an option.
	 *
	 * @param	string	$label
	 * @param	string	$description
	 * @return	Filters\Filter
	 */
	public function option($label, $description) {
		return new Filters\Option($this, $label, $description);
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
		return new Filters\Multiselect($this, $label, $description, $options);
	}

	/**
	 * Get a filter that uses some text for filtering.
	 *
	 * @param	string	$label
	 * @param	string	$description
	 * @return	Filters\Filter
	 */
	public function text($label, $description) {
		return new Filters\Text($this, $label, $description);
	}

	/**
	 * Get a filter where the given filters are included one after another.
	 *
	 * @param	Filters\Filter[]	...
	 * @return	Filters\Filter
	 */
	public function sequence() {
		$subs = func_get_args();
		return new Filters\Sequence($this, $subs);
	}

	/**
	 * Get a filter where the given filters are included one after another and
	 * and the resulting predicates are connected via AND.
	 *
	 * @param	Filters\Filter[]	...
	 * @return	Filters\Filter
	 */
	public function sequence_and() {
		$subs = func_get_args();
		$t = $this->type_factory()->cls("\\CaT\\Filter\\Predicates\\Predicate");
		assert('$this->sequence_and_check_input_content_type($subs,$t)');
		return call_user_func_array(array($this, "sequence"), $subs)
			->map_raw(function() {
				$preds = func_get_args();
				$f = $this->predicate_factory();
				return call_user_func_array(array($f, "_ALL"), $preds);
			}, $t);
	}

	private function sequence_and_check_input_content_type($subs,$t) {
		foreach ($subs as $sub) {
			if ($sub->content_type() != $t) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Get a filter where one of the sub filters is used.
	 *
	 * @param	string		$label
	 * @param	string		$description
	 * @param	Filter[]	...
	 * @return	Filter
	 */
	public function one_of($label, $description/*, ... $filters */) {
		$subs = func_get_args();
		$label = array_shift($subs);
		$description = array_shift($subs);
		return new Filters\OneOf($this, $label, $description, $subs);
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
		
		return function(\DateTime $start, \DateTime $end)  use ($field_start, $field_end, $f) {
			return		$f->field($field_start)->LE()->date($end)
				->_AND(	$f->date($start)->LE()->field($field_end));
		};
	}

	/**
	 * Map this over a text filter to get the standard behaviour where
	 * a field is compared with the filter.
	 *
	 * @param	string	$field
	 * @return	\Closure
	 */
	public function text_equals($field) {
		$f = $this->predicate_factory();

		return function($text) use ($field, $f) {
			return $f->field($field)->EQ()->str($text);
		};
	}

	/**
	 * Map this over a text filter to get the standard behaviour where
	 * a field is LIKE-compared with the filter.
	 *
	 * @param	string	$field
	 * @return	\Closure
	 */
	public function text_like($field) {
		$f = $this->predicate_factory();

		return function($text) use ($field, $f) {
			return $f->field($field)->LIKE()->str($text);
		};
	}
}