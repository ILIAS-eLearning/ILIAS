<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Icon;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

class Custom extends Icon implements C\Icon\Custom {

	/**
	 * @var	string
	 */
	private $icon_path;


	public function __construct($icon_path, $aria_label, $size, $is_disabled) {
		$this->checkStringArg("string", $icon_path);
		$this->checkStringArg("string", $aria_label);
		$this->checkArgIsElement(
			"size", $size,
			self::$possible_sizes,
			implode("/", self::$possible_sizes)
		);
		$this->checkBoolArg("is_disabled", $is_disabled);
		$this->name = 'custom';
		$this->icon_path = $icon_path;
		$this->aria_label = $aria_label;
		$this->size = $size;
		$this->is_disabled = $is_disabled;
	}

	/**
	 * @inheritdoc
	 */
	public function getIconPath() {
		return $this->icon_path;
	}

}
