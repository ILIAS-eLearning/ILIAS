<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;

/**
 * Loads renderers for components from the file system.
 *
 * To introduce a component that may react on the context of the rendering, you need to:
 *
 * * create a new implementation of RendererFactory in the implementation folder of that
 *   component
 * * introduce it as a dependency of this loader (and load at ilInitialisation::initUIFramework)
 * * make a special case for the components the new factory may create renderers for in
 *   FSLoader::getRendererFactoryFor
 */
class FSLoader implements Loader
{
    use LoaderHelper;

    /**
     * @var	DefaultRendererFactory
     */
    private $default_renderer_factory;

    /**
     * @var	RendererFactory
     */
    private $glyph_renderer_factory;

    public function __construct(
        RendererFactory $default_renderer_factory,
        RendererFactory $glyph_renderer_factory
    ) {
        $this->default_renderer_factory = $default_renderer_factory;
        $this->glyph_renderer_factory = $glyph_renderer_factory;
    }

    /**
     * @inheritdocs
     */
    public function getRendererFor(Component $component, array $contexts)
    {
        $context_names = $this->getContextNames($contexts);
        $factory = $this->getRendererFactoryFor($component);
        return $factory->getRendererInContext($component, $context_names);
    }

    /**
     * @inheritdocs
     */
    public function getRendererFactoryFor(Component $component)
    {
        if ($component instanceof \ILIAS\UI\Implementation\Component\Glyph\Glyph) {
            return $this->glyph_renderer_factory;
        }
        return $this->default_renderer_factory;
    }
}
