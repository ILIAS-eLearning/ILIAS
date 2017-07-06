<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Component\Input as I;
use ILIAS\Data;

class Factory implements I\Factory {
	/**
 	 * @var	Data\Factory
	 */
	protected $data_factory;

	public function __construct() {
		// TODO: This is not too good. Maybe we should give a DIC container.
		$this->data_factory = new Data\Factory;
	}

	/**
	 * @inheritdoc
	 */
	public function text($label, $byline = null) {
		return new Text($this->data_factory, $label, $byline);
	}
}
