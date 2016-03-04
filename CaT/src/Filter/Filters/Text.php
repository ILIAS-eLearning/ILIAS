<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Text extends Filter {
	public function __construct(\CaT\Filter\FilterFactory $factory, $label, $description,
								array $mappings = array(), array $mapping_result_types = array()) {
		assert('is_string($label)');
		assert('is_string($description)');

		$this->setFactory($factory);
		$this->setLabel($label);
		$this->setDescription($description);
		$this->setMappings($mappings, $mapping_result_types);
	}

	/**
	 * @inheritdocs
	 */
	public function original_content_type() {
		return $this->factory->type_factory()->string();
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
	 * @inheritdocs
	 */
	protected function clone_with_new_mappings($mappings, $mapping_result_types) {
		return new Text($this->factory, $this->label(), $this->description(),
						$mappings, $mapping_result_types);
	}
}
