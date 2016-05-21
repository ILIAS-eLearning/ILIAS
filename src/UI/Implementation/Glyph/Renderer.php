<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Glyph;

use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component;

class Renderer implements RendererInterface {
	/**
	 * @inheritdocs
	 */
	public function render(Component $component, RendererInterface $default_renderer) {
	}

	/**
	 * Get the css class used for a certain glyph type.
	 *
 	 * @param	mixed	$type
	 * @return	string
	 */
	static public function getCssClassFor($type) {
	}
}
