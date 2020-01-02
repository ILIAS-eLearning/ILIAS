<?php

/* Copyright (c) 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Render;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer;

/**
 * An entity that renders components to a string output.
 *
 * @author	Richard Klees <richard.klees@concepts-and-training.de>
 */
interface ComponentRenderer
{
    /**
     * Render the component if possible and delegate additional rendering to the
     * default_renderer.
     *
     * @param	Component 		$component
     * @param	Renderer		$default_renderer
     * @throws	\LogicException	if renderer is called with a component it can't render
     * @return	string
     */
    public function render(Component $component, Renderer $default_renderer);

    /**
     * Announce resources this renderer requires.
     *
     * @param	ResourceRegistry	$registry
     * @return	null
     */
    public function registerResources(ResourceRegistry $registry);
}
