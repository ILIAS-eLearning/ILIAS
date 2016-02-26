<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Singleselect extends Multiselect {
	
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
}
