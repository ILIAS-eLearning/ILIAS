<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

/**
 * An entity that renders elements to a string output.
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
class  DefaultRenderer {
	/**
	 * Render the element if possible and delegate additional rendering to the
	 * default_renderer.
	 *
	 * @param	$component			Component
	 * @param	$default_renderer	Renderer
	 * @return	string
	 */
	public function render(Component $component, Renderer $default_renderer) {
	}
}