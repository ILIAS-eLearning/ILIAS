<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Renderer;
use ILIAS\UI\Component;

/**
 * Renderer that dispatches rendering of UI components to a Renderer found
 * in the same namespace as the component to be renderered.
 */
class DefaultRenderer implements Renderer {
	/**
	 * @var	array<string, Renderer>
	 */
	protected $cache = array();

	/**
	 * @inheritdocs
	 */
	public function render(Component $component, Renderer $default_renderer) {
		$renderer = $this->getRendererFor(get_class($component));
		return $renderer->render($component, $this);
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
		if (array_key_exists($class, $this->cache)) {
			return $this->cache[$class];
		}
		$renderer = $this->instantiateRendererFor($class);
		$this->cache[$class] = $renderer;
		return $renderer;
	}

	/**
	 * Instantiate a renderer for a certain Component class.
	 *
	 * This will always create a fresh renderer for the component.
	 *
	 * @param	string	$class
	 * @throws	\LogicException		if no renderer could be found for component.
	 * @return	Renderer
	 */
	public function instantiateRendererFor($class) {
		$renderer_class = $this->getRendererNameFor($class);
		if (!class_exists($renderer_class)) {
			throw new \LogicException("No rendered for '".$class."' found.");
		}
		return new $renderer_class();
	}

	/**
	 * Get the class name for the renderer of Component class.
	 *
	 * @param	string	$class
	 * @return 	string
	 */
	public function	getRendererNameFor($class) {
		$parts = explode("\\", $class);
		$parts[count($parts)-1] = "Renderer";
		return implode("\\", $parts);
	}
}
