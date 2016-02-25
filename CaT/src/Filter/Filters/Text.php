<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Text extends Filter {
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
		return array("text");
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
	public function content(/*...$inputs*/) {
		$inputs = func_get_args();
		assert('count($inputs) == 1');
		assert('is_string($inputs[0])');

		return $inputs[0];
	}
}
