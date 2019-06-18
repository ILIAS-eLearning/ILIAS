<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Component\Input\Container\Form as F;
use ILIAS\UI\Implementation\Component\Input;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

class Factory implements F\Factory {
	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;
	/**
	 * @var Input\Field\Factory
	 */
	protected $field_factory;

	public function __construct(
		SignalGeneratorInterface $signal_generator,
		Input\Field\Factory $field_factory
	) {
		$this->signal_generator = $signal_generator;
		$this->field_factory = $field_factory;
	}

	/**
	 * @inheritdoc
	 */
	public function standard($post_url, array $inputs) {
		return new Standard($this->signal_generator, $this->field_factory, $post_url, $inputs);
	}
}
