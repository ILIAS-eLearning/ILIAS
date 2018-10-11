<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component as C;

class Standard extends Button implements C\Button\Standard {

	/**
	 * @var bool
	 */
	protected $anim = false;

	/**
	 * @inheritdoc
	 */
	public function withLoadingAnimation($anim) {
		$this->checkBoolArg("state", $anim);
		$clone = clone $this;
		$clone->anim =$anim;
		return $clone;
	}

	/**
	 * @inheritdoc
	 */
	public function hasLoadingAnimation() {
		return $this->anim;
	}
}
