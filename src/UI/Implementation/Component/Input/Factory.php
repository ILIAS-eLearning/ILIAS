<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Component\Input as I;
use ILIAS\Data;
use ILIAS\Validation;
use ILIAS\Transformation;

class Factory implements I\Factory {
	/**
 	 * @var	Data\Factory
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

	public function __construct() {
		// TODO: This is not too good. Maybe we should give a DIC container.
		$this->data_factory = new Data\Factory;
		$this->validation_factory= new Validation\Factory($this->data_factory);
		$this->transformation_factory = new Transformation\Factory;
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
		return new Group($this->data_factory,$this->validation_factory,$this->transformation_factory,$inputs, "", "");
	}

	/**
	 * @inheritdoc
	 */
	public function section(array $inputs, $label, $byline = null) {
		return new Section($this->data_factory,$this->validation_factory,$this->transformation_factory,$inputs, $label, $byline);
	}
}
