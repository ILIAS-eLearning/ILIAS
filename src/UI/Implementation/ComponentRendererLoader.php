<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

/**
 * Loads renderers for components.
 */
interface ComponentRendererLoader {
	/**
	 * Get a renderer for a certain Component class.
	 *
	 * @param	string	$class
	 * @throws	\LogicException		if no renderer could be found for component.
	 * @return	ComponentRenderer
	 */
	public function getRendererFor($class);
}
