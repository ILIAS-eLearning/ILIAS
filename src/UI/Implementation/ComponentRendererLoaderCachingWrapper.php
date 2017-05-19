<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation;

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
	public function getRendererFor($class) {
		if (isset($this->cache[$class])) {
			return $this->cache[$class];
		}
		$renderer = $this->loader->getRendererFor($class);
		$this->cache[$class] = $renderer;
		return $renderer;
    }
}
