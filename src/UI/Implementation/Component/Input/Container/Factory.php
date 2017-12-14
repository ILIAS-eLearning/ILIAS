<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container;

use ILIAS\UI\Component\Input as I;
use ILIAS\Data;
use ILIAS\Validation;
use ILIAS\Transformation;

class Factory implements I\Container\Factory {

	/**
	 * @inheritdoc
	 */
	public function form() {
		return new Form\Factory();
	}
}
