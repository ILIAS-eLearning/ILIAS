<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

abstract class SelectBase extends Filter {
	/**
	 * @var	array
	 */
	protected $options;

	public function __construct(\CaT\Filter\FilterFactory $factory, $label, $description, $options,
								array $mappings = array(), array $mapping_result_types = array()) {
		assert('is_string($label)');
		assert('is_string($description)');

		$this->setFactory($factory);
		$this->setLabel($label);
		$this->setDescription($description);
		$this->setMappings($mappings, $mapping_result_types);

		$this->options = $options;
	}

	/**
	 * @inheritdocs
	 */
	public function original_content_type() {
		return $this->content_type;
	}

	/**
	 * @inheritdocs
	 */
	public function input_type() {
		return $this->original_content_type();
	}

	/**
	 * @inheritdocs
	 */
	protected function raw_content($input) {
		return $input;
	}

	/**
	 * Get the options that could be selected.
	 *
	 * @return	int[]|string[]
	 */
	public function options() {
		return $this->options;
	}
}