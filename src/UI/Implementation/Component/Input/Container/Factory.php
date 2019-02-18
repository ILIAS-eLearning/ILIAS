<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container;

use ILIAS\UI\Component\Input as I;
use ILIAS\Data;
use ILIAS\Validation;
use ILIAS\Refinery\Transformation;

class Factory implements I\Container\Factory {
	/**
	 * @var Form\Factory
	 */
	protected $form_factory;
	/**
	 * @var Filter\Factory
	 */
	protected $filter_factory;

	public function __construct(
		Form\Factory $form_factory,
		Filter\Factory $filter_factory
	) {
		$this->form_factory = $form_factory;
		$this->filter_factory = $filter_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function form() {
		return $this->form_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function filter() {
		return $this->filter_factory;
	}
}
