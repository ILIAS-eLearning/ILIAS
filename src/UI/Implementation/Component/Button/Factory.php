<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button;

use ILIAS\UI\Component\Button as B;

class Factory implements B\Factory {
	/**
	 * @inheritdoc
	 */
	public function standard($label_or_glyph, $action) {
        return new Standard($label_or_glyph, $action);
    }

	/**
	 * @inheritdoc
	 */
	public function primary($label_or_glyph, $action) {
        return new Primary($label_or_glyph, $action);
    }

	/**
	 * @inheritdoc
	 */
	public function close() {
		throw new \ILIAS\UI\NotImplementedException();
    }
}
