<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container;

use ILIAS\UI\Component\Input as I;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\Data;
use ILIAS\Validation;
use ILIAS\Transformation;

class Factory implements I\Container\Factory {
	/**
	 * @var Form\Factory
	 */
	protected $form_factory;

	public function __construct(
		Form\Factory $form_factory
	) {
		$this->form_factory = $form_factory;
	}

	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;
	/**
	 * @var I\Field\Factory
	 */
	protected $field_factory;
	/**
	 * @var Factory
	 */
	protected $container_factory;

	/**
	 * Factory constructor.
	 *
	 * @param SignalGeneratorInterface $signal_generator
	 * @param \ILIAS\UI\Component\Input\Field\Factory $field_factory
	 * @param \ILIAS\UI\Component\Input\Container\Factory $container_factory
	 */
	public function __construct(SignalGeneratorInterface $signal_generator, I\Field\Factory $field_factory) {
		$this->signal_generator = $signal_generator;
		$this->field_factory = $field_factory;
		$this->container_factory = $this;
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
		return new Filter\Factory($this->signal_generator, $this->field_factory, $this->container_factory);
	}
}
