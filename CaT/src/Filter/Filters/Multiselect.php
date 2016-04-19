<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Multiselect extends SelectBase {
	public function __construct(\CaT\Filter\FilterFactory $factory, $label, $description, $options,
								$default_choice = "", array $mappings = array(),
								array $mapping_result_types = array()) {
		assert('is_string($label)');
		assert('is_string($description)');

		parent::__construct($factory, $label, $description, $options, $default_choice , $mappings, $mapping_result_types);

		$keys = array_keys($options);
		$tf = $factory->type_factory();
		if ($tf->lst($tf->int())->contains($keys)) {
			$this->content_type = $tf->lst($tf->int());
		}
		else if ($tf->lst($tf->string())->contains($keys)) {
			$this->content_type = $tf->lst($tf->string());
		}
		else {
			throw new \InvalidArgumentException("Use only strings or only ints as keys for options.");
		}
	}

	/**
	 * Set or get the default choice of options for the multiselect.
	 *
	 * @param	int[]|string[]|null		$default_choice
	 * @return	Multiselect|string[]|int[]
	 */
	public function default_choice(array $default_choice = null) {
		if ($default_choice === null) {
			return $this->default_choice;
		}

		list($ms, $mrts) = $this->getMappings();
		return new Multiselect($this->factory, $this->label(), $this->description(),
						$this->options, $default_choice, $ms, $mrts);
	}

	/**
	 * @inheritdocs
	 */
	protected function clone_with_new_mappings($mappings, $mapping_result_types) {
		return new Multiselect($this->factory, $this->label(), $this->description(),
						$this->options, $this->default_choice, $mappings, $mapping_result_types);
	}

	public function use_all_if_nothing(array $values, \CaT\Filter\Types\Type $result_type) {
		return $this->map(function(array $status) use ($values) {
			if (count($status) === 0) {
				return $values;
			}
			return $status;
		}, $result_type);
	}
}
