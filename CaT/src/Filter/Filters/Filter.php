<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

abstract class Filter {
	/**
	 * @var	\CaT\Filter\FilterFactory
	 */
	protected $factory;

	/**
	 * @var	string|null
	 */
	private $label = null;

	/**
	 * @var string|null
	 */
	private $description = null;

	protected function setFactory(\CaT\Filter\PredicateFactory $factory) {
		$this->factory = $factory;
	}

	protected function setLabel($label) {
		assert('is_string($label) || is_null($label)');
		$this->label = $label;
	}

	protected function setDescription($description) {
		assert('is_string($description) || is_null($description)');
		$this->description = $description;
	}

	/**
	 * The label is a _short_ text to label the filter.
	 *
	 * @return string|null
	 */
	public function label() {
		return $this->label;
	}

	/**
	 * The description is a text that explains the purpose of the filter.
	 *
	 * @return string|null
	 */
	public function description() {
		return $this->description;
	}

	/**
	 * Type of the content of the filter.
	 *
	 * @return	string[]
	 */
	abstract public function content_type();

	/**
	 * The type of inputs the filter requires.
	 *
	 * @return	string[]
	 */
	abstract public function input_type();

	/**
	 * Map a function over the content of the filter.
	 *
	 * @param	\Closure	$mapper
	 * @return	Filter
	 */
	public function map(\Closure $mapper) {
		
	}

	/**
	 * Get the content of the filter by supplying it with the required
	 * arguments.
	 *
	 * @param	mixed[]		...
	 * @return	mixed
	 */
	abstract public function content(/*...$input*/);
}