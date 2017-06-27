<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Component\Input as I;

class Factory implements I\Factory {
	/**
	 * @inheritdoc
	 */
	public function text($label, $byline = null) {
		return new Text($label, $byline);
	}
}
