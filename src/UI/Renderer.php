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
	 * Render given component. If an array of components is passed, this method returns an array containing the output
	 * of the rendered components, same index.
	 *
	 * @param Component\Component|Component\Component[] $component
	 * @param Component\Connector\ComponentConnection|Component\Connector\ComponentConnection[] $connection
	 *
	 * @return string|array The output of the renderer rendering the component
	 */
	public function render($component, $connection = null);
}
