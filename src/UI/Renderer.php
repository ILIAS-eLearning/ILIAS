<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

/**
 * An entity that renders elements to a string output.
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
interface Renderer {
	/**
	 * Render the element if possible and delegate additional rendering to the
	 * default_renderer.
	 *
	 * @param	$component			Component
	 * @param	$default_renderer	Renderer
	 * @throws	\LogicException		if renderer is called with a component it can't render
	 * @return	string
	 */
	public function render(Component $component, Renderer $default_renderer);
}
