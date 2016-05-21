<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Counter;

use ILIAS\UI\Component as C;

class Factory implements \ILIAS\UI\Factory\Counter {
	/**
	 * @inheritdoc
	 */
	public function status($number) {
		return new Counter(C\Counter::STATUS, $number);
	}

	/**
	 * @inheritdoc
	 */
	public function novelty($number) {
		return new Counter(C\Counter::NOVELTY, $number);
	}
}
