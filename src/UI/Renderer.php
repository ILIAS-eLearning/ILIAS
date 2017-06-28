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
	 * Render given component. If an array of components is passed, this method returns a concatenated output of
	 * each rendered component, in the same order as given in the array
	 *
	 * @param Component\Component|Component\Component[] $component
	 *
	 * @return string
	 */
	public function render($component);

	/**
	 * Same as render, except that this version also returns any javascript code bound to the on load event,
	 * wrapped in a script tag.
	 *
	 * @param Component\Component|Component\Component[] $component
	 * @return string
	 */
	public function renderAsync($component);
}
