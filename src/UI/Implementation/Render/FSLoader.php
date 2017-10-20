<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;

/**
 * Loads renderers for components from the file system.
 */
class FSLoader implements Loader {
	use LoaderHelper;

	/**
	 * @var	DefaultRendererFactory
	 */
	private $default_renderer_factory;

	public function __construct(DefaultRendererFactory $default_renderer_factory) {
		$this->default_renderer_factory = $default_renderer_factory;
    }

	/**
	 * @inheritdocs
	 */
	public function getRendererFor(Component $component, array $contexts) {
		$context_names = $this->getContextNames($contexts);
		$factory = $this->getRendererFactoryFor($component);
		return $factory->getRendererInContext($component, $context_names);
    }

	/**
	 * @inheritdocs
	 */
	public function getRendererFactoryFor(Component $component) {
		return $this->default_renderer_factory;
	}
}
