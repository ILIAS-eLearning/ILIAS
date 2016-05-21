<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Renderer;
use ILIAS\UI\Component;

/**
 * An entity that renders elements to a string output.
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
class  DefaultRenderer implements Renderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component $component, Renderer $default_renderer) {
	}
}
