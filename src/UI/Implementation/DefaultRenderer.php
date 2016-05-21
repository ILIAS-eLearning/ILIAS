<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Renderer;
use ILIAS\UI\Component;

/**
 * Renderer that dispatches rendering of UI components to a Renderer found
 * in the same namespace as the component to be renderered.
 */
class  DefaultRenderer implements Renderer {
	/**
	 * @inheritdocs
	 */
	public function render(Component $component, Renderer $default_renderer) {
	}

	/**
	 * Get a renderer for a certain Component class.
	 *
	 * Either initializes a new renderer or uses a cached one initialized
	 * before.
	 *
	 * @param	string	$class
	 * @throws	\LogicException		if no renderer could be found for component.
	 * @return	Renderer
	 */
	public function getRendererFor($class) {
	}

	/**
	 * Instantiate a renderer for a certain Component class.
	 *
	 * This will always create a fresh renderer for the component.
	 *
	 * @param	string	$class
	 * @throws	\LogicException		if no renderer could be found for component.
	 * @return Renderer
	 */
	public function instantiateRendererFor($class) {
	}
}
