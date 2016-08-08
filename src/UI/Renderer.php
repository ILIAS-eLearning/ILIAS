<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI;

/**
 * An entity that renders components to a string output.
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
interface Renderer {
	/**
	 * Render given component.
	 *
	 * @param	$component			Component
	 * @throws	\LogicException		if renderer is called with a component it can't render
	 * @return	string
	 */
	public function render(Component\Component $component);
}
