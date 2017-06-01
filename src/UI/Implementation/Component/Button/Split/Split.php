<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button\Split;

use ILIAS\UI\Component as C;

/**
 * This implements commonalities between standard and primary buttons.
 */
abstract class Split implements C\Button\Split\Split {

	/**
	 * @var bool
	 */
	protected $active = true;

	/**
	 * @inheritdoc
	 */
	public function isActive() {
		return $this->active;
	}

	/**
	 * @inheritdoc
	 */
	public function withUnavailableAction() {
		$clone = clone $this;
		$clone->active = false;
		return $clone;
	}
}
