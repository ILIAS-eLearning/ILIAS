<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

class Factory implements \ILIAS\UI\Factory {
	/**
	 * @return \ILIAS\UI\Factory\Counter
	 */
	public function counter() {
		return new Counter\Factory();
	}


	/**
	 * @return \ILIAS\UI\Factory\Glyph
	 */
	public function glyph() {
		return new Glyph\Factory();
	}
}