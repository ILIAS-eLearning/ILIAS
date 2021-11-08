<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;

/**
 * Registers resources for retreived renderers at a ResourceRegistry.
 */
class LoaderResourceRegistryWrapper implements Loader
{
    private ResourceRegistry $resource_registry;
    private Loader $loader;

    public function __construct(ResourceRegistry $resource_registry, Loader $loader)
    {
        $this->resource_registry = $resource_registry;
        $this->loader = $loader;
    }

    /**
     * @inheritdocs
     */
    public function getRendererFor(Component $component, array $contexts) : ComponentRenderer
    {
        $renderer = $this->loader->getRendererFor($component, $contexts);
        $renderer->registerResources($this->resource_registry);
        return $renderer;
    }

    /**
     * @inheritdocs
     */
    public function getRendererFactoryFor(Component $component) : RendererFactory
    {
        return $this->loader->getRendererFactoryFor($component);
    }
}
