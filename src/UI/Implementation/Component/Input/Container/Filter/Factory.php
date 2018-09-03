<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Filter;

use ILIAS\UI\Component\Input\Container\Filter as F;

class Factory implements F\Factory {

	/**
	 * @inheritdoc
	 */
	public function standard(array $inputs) {
		return new Standard($inputs);
	}
}