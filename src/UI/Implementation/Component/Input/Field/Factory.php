<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field;
use ILIAS\Data;
use ILIAS\Validation;
use ILIAS\Transformation;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;

/**
 * Class Factory
 *
 * @package ILIAS\UI\Implementation\Component\Input\Field
 */
class Factory implements Field\Factory {

	/**
	 * @var    Data\Factory
	 */
	protected $data_factory;
	/**
	 * @var Validation\Factory
	 */
	protected $validation_factory;
	/**
	 * @var Transformation\Factory
	 */
	protected $transformation_factory;
	/**
	 * @var SignalGeneratorInterface
	 */
	protected $signal_generator;


	/**
	 * Factory constructor.
	 *
	 * @param SignalGeneratorInterface $signal_generator
	 */
	public function __construct(SignalGeneratorInterface $signal_generator) {
		// TODO: This is not too good. Maybe we should give a DIC container.
		$this->data_factory = new Data\Factory;
		$this->validation_factory = new Validation\Factory($this->data_factory);
		$this->transformation_factory = new Transformation\Factory;
		$this->signal_generator = $signal_generator;
	}


	/**
	 * @inheritdoc
	 */
	public function text($label, $byline = null) {
		return new Text($this->data_factory, $this->validation_factory, $this->transformation_factory, $label, $byline);
	}


	/**
	 * @inheritdoc
	 */
	public function numeric($label, $byline = null) {
		return new Numeric($this->data_factory, $this->validation_factory, $this->transformation_factory, $label, $byline);
	}


	/**
	 * @inheritdoc
	 */
	public function group(array $inputs) {
		return new Group($this->data_factory, $this->validation_factory, $this->transformation_factory, $inputs, "", "");
	}


	/**
	 * @inheritdoc
	 */
	public function section(array $inputs, $label, $byline = null) {
		return new Section($this->data_factory, $this->validation_factory, $this->transformation_factory, $inputs, $label, $byline);
	}


	/**
	 * @inheritdoc
	 */
	public function dependantGroup(array $inputs) {
		return new DependantGroup($this->data_factory, $this->validation_factory, $this->transformation_factory, $this->signal_generator, $inputs);
	}


	/**
	 * @inheritdoc
	 */
	public function checkbox($label, $byline = null) {
		return new Checkbox($this->data_factory, $this->validation_factory, $this->transformation_factory, $label, $byline);
	}
}
