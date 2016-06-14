<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Renderer;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\ComponentRenderer;
use ILIAS\UI\Implementation\Render\TemplateFactory;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Factory as RootFactory;

/**
 * Renderer that dispatches rendering of UI components to a Renderer found
 * in the same namespace as the component to be renderered.
 */
class DefaultRenderer implements Renderer {
	/**
	 * @var	RootFactory
	 */
	protected $ui_factory;

	/**
	 * @var	array<string, ComponentRenderer>
	 */
	protected $cache = array();

	/**
	 * @var	TemplateFactory
	 */
	protected $tpl_factory;

	/**
	 * @var	ResourceRegistry
	 */
	protected $resource_registry;

	public function __construct(RootFactory $ui_factory, TemplateFactory $tpl_factory, ResourceRegistry $resource_registry) {
		$this->ui_factory = $ui_factory;
		$this->tpl_factory = $tpl_factory;
		$this->resource_registry = $resource_registry;
	}

	/**
	 * @inheritdocs
	 */
	public function render(Component $component) {
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
	 * @return	ComponentRenderer
	 */
	public function getRendererFor($class) {
		if (array_key_exists($class, $this->cache)) {
			return $this->cache[$class];
		}
		$renderer = $this->instantiateRendererFor($class);
		$renderer->registerResources($this->resource_registry);
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
	 * @return	ComponentRenderer
	 */
	public function instantiateRendererFor($class) {
		$renderer_class = $this->getRendererNameFor($class);
		if (!class_exists($renderer_class)) {
			throw new \LogicException("No rendered for '".$class."' found.");
		}
		return new $renderer_class($this->ui_factory, $this->tpl_factory);
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
