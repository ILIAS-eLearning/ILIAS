<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Implementation\Render\ResourceRegistry;

/**
 * Registers resources for retreived renderers at a ResourceRegistry.
 */
class ComponentRendererLoaderResourceRegistryWrapper implements ComponentRendererLoader {
	/**
	 * @var	ResourceRegistry
	 */
	private $resource_registry;

	/**
	 * @var ComponentRendererLoader
	 */
	private $loader;

	public function __construct(ResourceRegistry $resource_registry, ComponentRendererLoader $loader) {
		$this->resource_registry = $resource_registry;
		$this->loader = $loader;
    }

	/**
	 * @inheritdocs
	 */
	public function getRendererFor($class) {
		$renderer = $this->loader->getRendererFor($class);
		$renderer->registerResources($this->resource_registry);
		return $renderer;
    }
}
