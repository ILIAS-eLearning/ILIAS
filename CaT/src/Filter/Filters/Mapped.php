<?php

/* Copyright (c) 2016 Richard Klees, Extended GPL, see docs/LICENSE */

namespace CaT\Filter\Filters;

class Mapped extends Filter {
	public function __construct(\CaT\Filter\FilterFactory $factory, \Closure $mapper, $result_types) {
		$this->setFactory($factory);
	}

	/**
	 * @inheritdocs
	 */
	public function content_type() {
	}

	/**
	 * @inheritdocs
	 */
	public function input_type() {
	}

	/**
	 * @inheritdocs
	 */
	public function content(/*...$inputs*/) {
		$inputs = func_get_args();



		return $inputs;
	}
}
