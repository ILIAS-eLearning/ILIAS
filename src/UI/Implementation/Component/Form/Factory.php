<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Form;

use ILIAS\UI\Component\Form as F;

class Factory implements F\Factory {
	/**
	 * @inheritdoc
	 */
	public function standard($post_url, array $inputs) {
		return new Standard($post_url, $inputs);
	}
}
