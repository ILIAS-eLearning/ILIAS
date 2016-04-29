<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Singleselect extends SelectBase {
	/**
	 * @var	string
	 */
	protected $default_choice;

	public function __construct(\CaT\Filter\FilterFactory $factory, $label, $description, $options,
								array $mappings = array(), array $mapping_result_types = array(),
								$default_choice = "")
	{
		assert('is_string($label)');
		assert('is_string($description)');

		parent::__construct($factory, $label, $description, $options,  $mappings, $mapping_result_types);

		$keys = array_keys($options);
		$tf = $factory->type_factory();
		if ($tf->lst($tf->int())->contains($keys)) {
			$this->content_type = $tf->int();
		}
		else if ($tf->lst($tf->string())->contains($keys)) {
			$this->content_type = $tf->string();
		}
		else {
			throw new \InvalidArgumentException("Use only strings or only ints as keys for options.");
		}

		$this->default_choice = $default_choice;
	}

	/**
	 * Set or get the default choice of options for the multiselect.
	 *
	 * @param	int|string|null		$default_choice
	 * @return	Multiselect|string|int
	 */
	public function default_choice($default_choice = null) {
		if ($default_choice === null) {
			return $this->default_choice;
		}

		list($ms, $mrts) = $this->getMappings();
		return new Singleselect($this->factory, $this->label(), $this->description(),
						$this->options, $ms, $mrts, $default_choice);
	}

	/**
	 * @inheritdocs
	 */
	protected function clone_with_new_mappings($mappings, $mapping_result_types) {
		return new Singleselect($this->factory, $this->label(), $this->description(),
						$this->options, $mappings, $mapping_result_types, $this->default_choice);
	}
}
