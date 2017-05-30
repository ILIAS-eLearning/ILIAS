<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

use ILIAS\UI\Component\Component;

/**
 * Caches renderers loaded by another loader.
 */
class ComponentRendererLoaderCachingWrapper implements ComponentRendererLoader {
	use ComponentRendererLoaderHelper;

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
		$key = $this->getCacheKey($component, $contexts);
		if (isset($this->cache[$key])) {
			return $this->cache[$key];
		}
		$renderer = $this->loader->getRendererFor($component, $contexts);
		$this->cache[$key] = $renderer;
		return $renderer;
    }

	/**
	 * Get a key for the cache.
	 *
	 * @param	Component	$component
	 * @param	Component[]	$contexts
	 * @return 	string
	 */
	protected function getCacheKey(Component $component, array $contexts) {
		return $component->getCanonicalName()." ".implode("_", $this->getContextNames($contexts));
	}
}
