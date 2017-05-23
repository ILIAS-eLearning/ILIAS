<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Factory as RootFactory;
use ILIAS\UI\Component\Component;

/**
 * Loads renderers for components from the file system.
 */
class ComponentRendererFSLoader implements ComponentRendererLoader {
	/**
	 * @var	RootFactory
	 */
	private $ui_factory;

	/**
	 * @var	Render\TemplateFactory
	 */
	private $tpl_factory;

	/**
	 * @var	\ilLanguage
	 */
	private $lng;

	/**
	 * @var	Render\JavaScriptBinding
	 */
	private $js_binding;

	public function __construct(RootFactory $ui_factory, Render\TemplateFactory $tpl_factory, \ilLanguage $lng, Render\JavaScriptBinding $js_binding) {
        $this->ui_factory = $ui_factory;
        $this->tpl_factory = $tpl_factory;
        $this->lng = $lng;
        $this->js_binding = $js_binding;
    }

	/**
	 * @inheritdocs
	 */
	public function getRendererFor(Component $component, array $contexts) {
		$class = get_class($component);
		$renderer_class = $this->getRendererNameFor($class);
		if (!class_exists($renderer_class)) {
			throw new \LogicException("No rendered for '".$class."' found.");
		}
		return new $renderer_class($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);
    }

	/**
	 * Instantiate a renderer for a certain Component class.
	 *
	 * This will always create a fresh renderer for the component.
	 *
	 * @param	string	$class
	 * @throws	\LogicException		if no renderer could be found for component.
	 * @return	ComponentRenderer
	 */
	protected function instantiateRendererFor($class) {
		$renderer_class = $this->getRendererNameFor($class);
		if (!class_exists($renderer_class)) {
			throw new \LogicException("No rendered for '".$class."' found.");
		}
		return new $renderer_class($this->ui_factory, $this->tpl_factory, $this->lng, $this->js_binding);
	}

	/**
	 * Get the class name for the renderer of Component class.
	 *
	 * @param	string	$class
	 * @return	string
	 */
	protected function getRendererNameFor($class) {
		$parts = explode("\\", $class);
		$parts[count($parts)-1] = "Renderer";
		return implode("\\", $parts);
	}
}
