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
	public function shy($label, $action) {
		return new Shy($label, $action);
	}
}
