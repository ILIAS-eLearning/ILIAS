<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Listing;

use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\ComponentHelper;

/**
 * List divider
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Divider implements C\Panel\Listing\Divider {
	use ComponentHelper;

	/**
	 * @var string
	 */
	protected  $label;

	public function __construct() {
	}

	/**
	 * @inheritdoc
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @inheritdoc
	 */
	public function withLabel($label) {
		$this->checkStringArg("label", $label);
		$clone = clone $this;
		$clone->label = (string) $label;
		return $clone;

	}

}
