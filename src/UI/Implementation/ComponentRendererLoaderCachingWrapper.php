<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Component\Component;

/**
 * Caches renderers loaded by another loader.
 */
class ComponentRendererLoaderCachingWrapper implements ComponentRendererLoader {
	/**
	 * @var ComponentRendererLoader	
	 */
	private $loader;

	/**
	 * @var	array<string, ComponentRenderer>
	 */
	private $cache = array();

	public function __construct(ComponentRendererLoader $loader) {
		$this->loader = $loader;
    }

	/**
	 * @inheritdocs
	 */
	public function getRendererFor(Component $component, array $contexts) {
		$class = get_class($component);
		if (isset($this->cache[$class])) {
			return $this->cache[$class];
		}
		$renderer = $this->loader->getRendererFor($component, $contexts);
		$this->cache[$class] = $renderer;
		return $renderer;
    }
}
