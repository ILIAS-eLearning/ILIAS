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

	/**
	 * @var \Closure[]
	 */
	private $mappings = array();

	/**
	 * @var Types\Type
	 */
	private $mapping_result_types = array();

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

	protected function setMappings(array $mappings, array $mapping_result_types) {
		assert('count($mappings) == count($mapping_result_types)');
		$this->mappings = array_map(function(\Closure $c){return $c;}, $mappings);
		$this->mapping_result_types = array_map(function(\CaT\Filter\Types\Type $t){return $t;}, $mapping_result_types);
	}

	protected function getMappings() {
		return array($this->mappings, $this->mapping_result_types);
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
	public function content_type() {
		if (count($this->mapping_result_types) == 0) {
			return $this->original_content_type();
		}
		else {
			return end($this->mapping_result_types);
		}
	}

	/**
	 * Type of the original (i.e. unmapped) content of the filter.
	 *
	 * @return	\CaT\Filter\Types\Type
	 */
	abstract public function original_content_type();

	/**
	 * The type of inputs the filter requires.
	 *
	 * @return	\CaT\Filter\Types\Type
	 */
	abstract public function input_type();

	/**
	 * Map a function over the content of the filter.
	 *
	 * TODO: switch params
	 *
	 * @param	\Closure					$mapper
	 * @param	\CaT\Filter\Types\Type		$result_type
	 * @return	Filter
	 */
	public function map(\Closure $mapper, \CaT\Filter\Types\Type $result_type) {
		assert('$mapper instanceof \\Closure');
		//TODO : check number of $mapper params.
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
		$ms = array_merge($this->mappings, array($mapper));
		$mrts = array_merge($this->mapping_result_types, array($result_type));
		return $this->clone_with_new_mappings($ms, $mrts);
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
		return $this->mapped_content($structured);
	}

	/**
	 * Like raw_content, but result is mapped.
	 *
	 * @param	mixed		$input
	 * @return	mixed
	 */
	protected function mapped_content($input) {
		$content = $this->raw_content($input);
		$content_type = $this->original_content_type();
		$len = count($this->mappings);

		for ($i = 0; $i < $len; $i++) {
			$mapping = $this->mappings[$i];
			$flattened_content = $content_type->flatten($content);
			$content = call_user_func_array($mapping, $flattened_content);
			$content_type = $this->mapping_result_types[$i];
			if(!$content_type->contains($content) ) {
				throw new \InvalidArgumentException
							("Expected ".$content_type->repr().", retreived ".
							print_r($content, true));
			}
		}
		return $content;
	}

	/**
	 * Like content but does expect an argument according to input_type;
	 *
	 * @param	mixed		$input
	 * @return	mixed
	 */
	abstract protected function raw_content($input);

	/**
	 * Create a copy of this filter with new mappings.
	 *
	 * @param	\Closure[]	$mappings
	 * @param	Types\Type	$mapping_result_types
	 * @return	self
	 */
	abstract protected function clone_with_new_mappings($mappings, $mapping_result_types);
}