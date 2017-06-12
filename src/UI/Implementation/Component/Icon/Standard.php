<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Icon;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Standard extends Icon implements C\Icon\Standard {

	public function __construct($class, $aria_label, $size) {
		$this->checkStringArg("string", $class);
		$this->checkStringArg("string", $aria_label);
		$this->checkArgIsElement(
			"size", $size,
			self::$possible_sizes,
			implode(self::$possible_sizes, '/')
		);
		$this->css_class = $class;
		$this->aria_label = $aria_label;
		$this->size = $size;

	}

}
