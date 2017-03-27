<?php

/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input;
use ILIAS\UI\Component as C;

/**
 * Class Factory for input components
 *
 * @author	Nils Haagen <nils.haagen@concepts-and-training.de>
 * @package ILIAS\UI\Implementation\Component\Input
 */
class Factory implements C\Input\Factory {

	/**
	 * @inheritdoc
	 */
	public function rating($topic, $captions=''){
		return new Rating\Rating($topic, $captions);
	}

}



