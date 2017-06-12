<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Icon;

use ILIAS\UI\Component\Icon as I;

class Factory implements I\Factory {
	/**
	 * @inheritdoc
	 */
	public function standard($class, $aria_label, $size='small', $abbreviation=''){
		return new Standard($class, $aria_label, $size, $abbreviation);
	}

	/**
	 * @inheritdoc
	 */
	public function custom($class, $aria_label, $size='small', $abbreviation=''){
		return new Custom($class, $aria_label, $size, $abbreviation);
	}

}
