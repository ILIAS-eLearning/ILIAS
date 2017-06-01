<?php

/* Copyright (c) 2017 Alex Killing <killing@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Button\Split;

use ILIAS\UI\Component\Button\Split as S;

class Factory implements S\Factory {
	/**
	 * @inheritdoc
	 */
	public function standard($actions) {
		return new Standard($actions);
	}

	/**
	 * @inheritdoc
	 */
	public function month($default) {
		return new Month($default);
	}
}
