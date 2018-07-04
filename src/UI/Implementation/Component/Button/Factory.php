<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component\Button as B;

class Factory implements B\Factory {
	/**
	 * @inheritdoc
	 */
	public function standard($label, $action) {
		return new Standard($label, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function primary($label, $action) {
		return new Primary($label, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function close() {
		return new Close();
	}

	/**
	 * @inheritdoc
	 */
	public function tag($label, $action) {
		return new Tag($label, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function shy($label, $action) {
		return new Shy($label, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function month($default) {
		return new Month($default);
	}

	/**
	 * @inheritdoc
	 */
	public function bulky($icon_or_glyph, $label, $action) {
		return new Bulky($icon_or_glyph, $label, $action);
	}

	/**
	 * @inheritdoc
	 */
	public function toggle($label, $action, $action_off, $is_on = false) {
		return new Toggle($label, $action, $action_off, $is_on);
	}
}
