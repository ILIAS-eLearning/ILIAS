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

	protected function setFactory(\CaT\Filter\FilterFactory $factory) {
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
	 * @return	\CaT\Filter\Types\Type
	 */
	abstract public function content_type();

	/**
	 * The type of inputs the filter requires.
	 *
	 * @return	\CaT\Filter\Types\Type
	 */
	abstract public function input_type();

	/**
	 * Map a function over the content of the filter.
	 *
	 * @param	\Closure					$mapper
	 * @param	\CaT\Filter\Types\Type		$result_type
	 * @return	Filter
	 */
	public function map(\Closure $mapper, \CaT\Filter\Types\Type $result_type) {
		assert('$mapper instanceof \\Closure');
		return $this->map_raw($mapper, $result_type);
	}

	/**
	 * Map a function over the content of the filter, but without any checks.
	 *
	 * @param	\Closure					$mapper
	 * @param	\CaT\Filter\Types\Type		$result_type
	 * @return	Filter
	 */
	public function map_raw(\Closure $mapper, \CaT\Filter\Types\Type $result_type) {
		return new Mapped($this->factory, $this, $mapper, $result_type);
	}


	/**
	 * Map the content of the filter to a predicate.
	 *
	 * @param	\Closure	$to_pred
	 * @return	Filter
	 */
	public function map_to_predicate(\Closure $mapper) {
		return $this->map($mapper, $this->factory->type_factory()->cls("\\CaT\\Filter\\Predicates\\Predicate"));
	}

	/**
	 * Get the content of the filter by supplying it with the required
	 * arguments.
	 *
	 * @param	mixed[]		...
	 * @return	mixed
	 */
	public function content(/*...$inputs*/) {
		$inputs = func_get_args();
		$structured = $this->input_type()->unflatten($inputs);
		return $this->_content($structured);
	}

	/**
	 * Like content but does expect an argument according to input_type;
	 *
	 * @param	mixed		$input
	 * @return	mixed
	 */
	abstract protected function _content($input);
}