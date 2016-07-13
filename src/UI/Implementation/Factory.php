<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

// TODO: This might cache the created factories.
class Factory implements \ILIAS\UI\Factory {
	/**
	 * @inheritdoc
	 */
	public function counter() {
		return new Component\Counter\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function glyph() {
		return new Component\Glyph\Factory();
	}

	/**
	 * @inheritdoc
	 */
	public function button() {
		return new Component\Button\Factory();
	}
}
