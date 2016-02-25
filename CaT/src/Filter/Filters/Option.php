<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Option extends Filter {
	public function __construct(\CaT\Filter\FilterFactory $factory, $label, $description) {
		assert('is_string($label)');
		assert('is_string($description)');

		$this->setFactory($factory);
		$this->setLabel($label);
		$this->setDescription($description);
	}

	/**
	 * @inheritdocs
	 */
	public function content_type() {
		return $this->factory->type_factory()->bool();
	}

	/**
	 * @inheritdocs
	 */
	public function input_type() {
		return $this->content_type();
	}

	/**
	 * @inheritdocs
	 */
	protected function _content($input) {
		return $input;
	}
}
