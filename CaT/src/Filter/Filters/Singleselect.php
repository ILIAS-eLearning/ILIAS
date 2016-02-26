<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Singleselect extends Multiselect {
	/**
	 * @var	array
	 */
	private $options;

	/**
	 * @var	int[]|string[]
	 */
	private $default_choice;

	public function __construct(\CaT\Filter\FilterFactory $factory, $label, $description, $options,
								$default_choice = array(), array $mappings = array(),
								array $mapping_result_types = array()) {
		assert('is_string($label)');
		assert('is_string($description)');

		$this->setFactory($factory);
		$this->setLabel($label);
		$this->setDescription($description);
		$this->setMappings($mappings, $mapping_result_types);

		$this->options = $options;
		$this->default_choice = $default_choice;
	}

	/**
	 * @inheritdocs
	 */
	public function original_content_type() {
		return $this->factory->type_factory()->string();
	}

	/**
	 * Get the options that could be selected.
	 *
	 * @return	int[]|string[]
	 */
	public function options() {
		return $this->options;
	}

	/**
	 * Set or get the default choice of options for the multiselect.
	 *
	 * @param	int[]|string[]|null		$options
	 * @return	Multiselect|string[]|int[]
	 */
	public function default_choice(array $options = null) {
		if ($options === null) {
			return $this->default_choice;
		}

		list($ms, $mrts) = $this->getMappings();
		return new Multiselect($this->factory, $this->label(), $this->description(),
						$options, $this->default_choice, $ms, $mrts);
	}
}
