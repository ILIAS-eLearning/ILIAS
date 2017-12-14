<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Container\Form;

use ILIAS\UI\Component\Input\Container\Form as F;

class Factory implements F\Factory {

	/**
	 * @inheritdoc
	 */
	public function standard($post_url, array $inputs) {
		return new Standard($post_url, $inputs);
	}
}
