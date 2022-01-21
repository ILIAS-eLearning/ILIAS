<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;

/**
 * Caches renderers loaded by another loader.
 */
class LoaderCachingWrapper implements Loader
{
    use LoaderHelper;

    private Loader $loader;

    /**
     * @var	array<string, ComponentRenderer>
     */
    private array $cache = array();

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @inheritdocs
     */
    public function getRendererFor(Component $component, array $contexts) : ComponentRenderer
    {
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
     * @param	Component[]	$contexts
     */
    protected function getCacheKey(Component $component, array $contexts) : string
    {
        return $component->getCanonicalName() . " " . implode("_", $this->getContextNames($contexts));
    }

    /**
     * @inheritdocs
     */
    public function getRendererFactoryFor(Component $component) : RendererFactory
    {
        return $this->loader->getRendererFactoryFor($component);
    }
}
