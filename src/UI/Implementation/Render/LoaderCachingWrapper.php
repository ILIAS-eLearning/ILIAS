<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
    public function getRendererFor(Component $component, array $contexts): ComponentRenderer
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
    protected function getCacheKey(Component $component, array $contexts): string
    {
        return $component->getCanonicalName() . " " . implode("_", $this->getContextNames($contexts));
    }

    /**
     * @inheritdocs
     */
    public function getRendererFactoryFor(Component $component): RendererFactory
    {
        return $this->loader->getRendererFactoryFor($component);
    }
}
