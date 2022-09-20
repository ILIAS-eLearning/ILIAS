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
    public function getRendererFor(Component $component, array $contexts): ComponentRenderer
    {
        $renderer = $this->loader->getRendererFor($component, $contexts);
        $renderer->registerResources($this->resource_registry);
        return $renderer;
    }

    /**
     * @inheritdocs
     */
    public function getRendererFactoryFor(Component $component): RendererFactory
    {
        return $this->loader->getRendererFactoryFor($component);
    }
}
