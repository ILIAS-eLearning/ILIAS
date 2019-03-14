<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Panel\Secondary;

use ILIAS\UI\Component as C;

/**
 * Class Factory
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Factory implements C\Panel\Secondary\Factory {

	/**
	 * @inheritdoc
	 */
	public function listing(string $title, array $items) {
		return new Listing($title, $items);
	}

	public function legacy(string $title, C\Legacy\Legacy $legacy) {
		return new Legacy($title, $legacy);
	}
}
